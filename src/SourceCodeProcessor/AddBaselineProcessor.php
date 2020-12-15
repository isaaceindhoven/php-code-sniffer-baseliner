<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\PhpTokenizer\TokenizedSourceCode;
use ISAAC\CodeSnifferBaseliner\PhpTokenizer\Tokenizer;

use function array_key_exists;
use function array_splice;
use function array_unique;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function preg_match;
use function sort;
use function sprintf;
use function substr;
use function trim;

use const PHP_EOL;

class AddBaselineProcessor
{
    const PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION
        = '`^(?<instruction>\\s*//\\s*phpcs:ignore)(?<rules> [^-]+)?( --(?<message>.*))?$`';

    /**
     * @param string[][] $ruleExclusionsByLineNumber
     */
    public function addRuleExclusionsByLineNumber(string $sourceCode, array $ruleExclusionsByLineNumber): string
    {
        $lines = explode(PHP_EOL, $sourceCode);
        $tokenizedSourceCode = (new Tokenizer())->tokenize($sourceCode);

        $addedLines = 0;
        foreach ($ruleExclusionsByLineNumber as $lineNumber => $ruleExclusions) {
            $addedLines += $this->addRuleExclusions(
                $lines,
                $lineNumber + $addedLines,
                $ruleExclusions,
                $tokenizedSourceCode,
                $lineNumber
            );
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Inserts line(s) with comments to ignore specific rules.
     * @param string[] &$lines
     * @param string[] $ruleExclusions
     * @return int number of lines inserted
     */
    private function addRuleExclusions(
        array &$lines,
        int $lineNumber,
        array $ruleExclusions,
        TokenizedSourceCode $originalTokenizedSourceCode,
        int $originalLineNumber
    ): int {
        if ($lineNumber === 1) {
            $lastTokenAtFirstLine = $originalTokenizedSourceCode->getLastTokenAtLine(1);
            // TODO: decide what to do when a multiline string starts on the first line
            if (!$lastTokenAtFirstLine->isPartOfString()) {
                $lines[0] .= sprintf(' %s', $this->generateInstructionComment($ruleExclusions, 'ignore'));
            }
            return 0;
        }

        if (!$this->canInsertCommentLineBefore($originalLineNumber, $originalTokenizedSourceCode)) {
            return $this->insertEnableDisableComments($lines, $originalTokenizedSourceCode, $lineNumber,
                $ruleExclusions);
        }

        if ($this->isIgnoreComment($lines[$lineNumber - 2])) {
            $lines[$lineNumber - 2] = $this->mergeIgnoreComment($lines[$lineNumber - 2], $ruleExclusions);
            return 0;
        }

        $commentStart = $this->determineCommentStartBeforeLine($originalLineNumber, $originalTokenizedSourceCode);
        $ignoreComment = $this->generateInstructionComment($ruleExclusions, 'ignore', $commentStart);
        $this->insertLineBefore($lines, $lineNumber, $ignoreComment);
        return 1;
    }

    private function generateInstructionComment(
        array $ruleExclusions,
        string $instruction,
        string $commentStart = '// '
    ): string {
        sort($ruleExclusions);
        return sprintf('%sphpcs:%s %s -- baseline', $commentStart, $instruction, implode(', ', $ruleExclusions));
    }

    private function canInsertCommentLineBefore(int $lineNumber, TokenizedSourceCode $tokenizedSourceCode): bool
    {
        // Comment line can be inserted after the last line
        if ($lineNumber > $tokenizedSourceCode->getMaximumEndingLineNumber()) {
            return true;
        }

        $firstTokenAtLineNumber = $tokenizedSourceCode->getFirstTokenEndingAtOrAfterLine($lineNumber);

        return !$firstTokenAtLineNumber->isPartOfString();
    }

    /**
     * @param string[] &$lines
     * @param string[] $ruleExclusions
     * @return int number of lines inserted
     */
    private function insertEnableDisableComments(
        array &$lines,
        TokenizedSourceCode $tokenizedSourceCode,
        int $violationLineNumber,
        array $ruleExclusions
    ): int {
        $disableCommentBeforeLineNumber = $violationLineNumber - 1;
        while (!$this->canInsertCommentLineBefore($disableCommentBeforeLineNumber, $tokenizedSourceCode)) {
            $disableCommentBeforeLineNumber--;
        }
        $disableComment = $this->generateInstructionComment($ruleExclusions, 'disable');
        $this->insertLineBefore($lines, $disableCommentBeforeLineNumber, $disableComment);

        $enableCommentBeforeLineNumber = $violationLineNumber + 1;
        while (!$this->canInsertCommentLineBefore($enableCommentBeforeLineNumber, $tokenizedSourceCode)) {
            $enableCommentBeforeLineNumber++;
        }
        $enableComment = $this->generateInstructionComment($ruleExclusions, 'enable');
        $this->insertLineBefore($lines, $enableCommentBeforeLineNumber + 1, $enableComment);

        return 2;
    }

    private function insertLineBefore(array &$lines, int $lineNumber, string $contents): void
    {
        $indentation = array_key_exists($lineNumber - 1, $lines)
            ? $this->determineIndentation($lines[$lineNumber - 1])
            : '';
        array_splice($lines, $lineNumber - 1, 0, $indentation . $contents);
    }

    private function determineIndentation(string $line): string
    {
        preg_match('`^(?<indent>\\s*)[^\\s]`', $line, $matches);
        if (!is_array($matches) || !array_key_exists('indent', $matches)) {
            return '';
        }
        return $matches['indent'];
    }

    private function isIgnoreComment(string $line): bool
    {
        return preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line) === 1;
    }

    private function mergeIgnoreComment(string $line, array $ruleExclusions): string
    {
        preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line, $matches);
        if (!is_array($matches) || !array_key_exists('instruction', $matches) || !is_string($matches['instruction'])) {
            // TODO throw exception + handle
        }

        $existingRuleExclusions = [];
        if (array_key_exists('rules', $matches) && is_string($matches['rules'])) {
            foreach (explode(',', $matches['rules']) as $rule) {
                $trimmedRule = trim($rule);
                if ($trimmedRule === '') {
                    continue;
                }
                $existingRuleExclusions[] = $trimmedRule;
            }
        }
        if (count($existingRuleExclusions) === 0) {
            // Do not modify line if no rules are specified
            return $line;
        }
        $ruleExclusions = array_unique(array_merge($existingRuleExclusions, $ruleExclusions));
        sort($ruleExclusions);

        $messages = [];
        if (array_key_exists('message', $matches) && is_string($matches['message'])) {
            $trimmedMessage = trim($matches['message']);
            if ($trimmedMessage !== '') {
                $messages[] = $trimmedMessage;
            }
        }
        $messages[] = 'baseline';

        return sprintf(
            '%s %s -- %s',
            $matches['instruction'],
            implode(', ', $ruleExclusions),
            implode('; ', $messages)
        );
    }

    private function determineCommentStartBeforeLine(int $lineNumber, TokenizedSourceCode $tokenizedSourceCode): string
    {
        $firstTokenEndingAtOrAfterLine = $tokenizedSourceCode->getFirstTokenEndingAtOrAfterLine($lineNumber);
        if ($firstTokenEndingAtOrAfterLine->isMultiLineComment()) {
            $commentLines = explode(PHP_EOL, $firstTokenEndingAtOrAfterLine->getContents());
            $commentLineContents = $lineNumber < $firstTokenEndingAtOrAfterLine->getEndingLineNumber()
                ? $commentLines[$lineNumber - $firstTokenEndingAtOrAfterLine->getStartingLineNumber()]
                : $commentLines[$lineNumber - $firstTokenEndingAtOrAfterLine->getStartingLineNumber() - 1];
            if (substr(trim($commentLineContents), 0, 2) === '* ') {
                return '* @';
            }
            return '';
        }

        return '// ';
    }
}
