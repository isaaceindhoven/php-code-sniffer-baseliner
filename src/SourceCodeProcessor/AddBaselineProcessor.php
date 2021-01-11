<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\PhpTokenizer\TokenizedSourceCode;
use ISAAC\CodeSnifferBaseliner\PhpTokenizer\Tokenizer;

use function array_key_exists;
use function array_splice;
use function explode;
use function implode;
use function is_array;
use function preg_match;
use function sort;
use function sprintf;
use function substr;
use function trim;

use const PHP_EOL;

class AddBaselineProcessor
{
    /**
     * @var IgnoreCommentLineParser
     */
    private $ignoreCommentParser;
    /**
     * @var IgnoreCommentLineMerger
     */
    private $ignoreCommentMerger;

    public function __construct(
        IgnoreCommentLineParser $ignoreCommentLineParser,
        IgnoreCommentLineMerger $ignoreCommentLineMerger
    ) {
        $this->ignoreCommentParser = $ignoreCommentLineParser;
        $this->ignoreCommentMerger = $ignoreCommentLineMerger;
    }

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
     * @param string[] $lines
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
        if ($originalLineNumber === 1) {
            $this->ignoreInline($lines, $originalTokenizedSourceCode, $originalLineNumber, $ruleExclusions);
            return 0;
        }

        if (!$this->canInsertCommentLineBefore($originalLineNumber, $originalTokenizedSourceCode)) {
            return $this->insertEnableDisableComments(
                $lines,
                $originalTokenizedSourceCode,
                $lineNumber,
                $ruleExclusions
            );
        }

        $lineBefore = &$lines[$lineNumber - 2];
        if ($this->ignoreCommentParser->isIgnoreComment($lineBefore)) {
            $lineBefore = $this->ignoreCommentMerger->mergeIgnoreCommentLine($lineBefore, $ruleExclusions);
            return 0;
        }

        $commentStart = $this->determineCommentStartBeforeLine($originalLineNumber, $originalTokenizedSourceCode);
        $ignoreComment = $this->generateInstructionComment($ruleExclusions, 'ignore', $commentStart);
        $this->insertLineBefore($lines, $lineNumber, $ignoreComment);
        return 1;
    }

    /**
     * @param string[] $lines
     * @param string[] $ruleExclusions
     */
    private function ignoreInline(
        array &$lines,
        TokenizedSourceCode $tokenizedSourceCode,
        int $lineNumber,
        array $ruleExclusions
    ): void {
        $lastTokenAtFirstLine = $tokenizedSourceCode->getLastTokenAtLine($lineNumber);
        if ($lastTokenAtFirstLine !== null && !$lastTokenAtFirstLine->isPartOfString()) {
            $lines[$lineNumber - 1] .= sprintf(' %s', $this->generateInstructionComment($ruleExclusions, 'ignore'));
        }
    }

    /**
     * @param string[] $ruleExclusions
     */
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

        return $firstTokenAtLineNumber !== null && !$firstTokenAtLineNumber->isPartOfString();
    }

    /**
     * @param string[] $lines
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

    /**
     * @param string[] $lines
     */
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

    private function determineCommentStartBeforeLine(int $lineNumber, TokenizedSourceCode $tokenizedSourceCode): string
    {
        $firstTokenEndingAtOrAfterLine = $tokenizedSourceCode->getFirstTokenEndingAtOrAfterLine($lineNumber);
        if ($firstTokenEndingAtOrAfterLine !== null && $firstTokenEndingAtOrAfterLine->isMultiLineComment()) {
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
