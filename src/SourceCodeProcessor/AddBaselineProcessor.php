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
use function is_string;
use function preg_match;
use function preg_split;
use function rtrim;
use function sort;
use function sprintf;
use function strlen;
use function substr;
use function trim;

use const PHP_EOL;

class AddBaselineProcessor
{
    /**
     * @var InstructionCommentLineParser
     */
    private $ignoreCommentParser;

    public function __construct(
        InstructionCommentLineParser $ignoreCommentLineParser
    ) {
        $this->ignoreCommentParser = $ignoreCommentLineParser;
    }

    /**
     * @param array<array<string>> $ruleExclusionsByLineNumber
     */
    public function addRuleExclusionsByLineNumber(string $sourceCode, array $ruleExclusionsByLineNumber): string
    {
        $lines = preg_split(sprintf("/\r?\n/"), $sourceCode);
        $tokenizedSourceCode = (new Tokenizer())->tokenize($sourceCode);

        $lineNumbersAdded = [];
        foreach ($ruleExclusionsByLineNumber as $originalLineNumber => $ruleExclusions) {
            $this->addRuleExclusions(
                $lines,
                $originalLineNumber,
                $ruleExclusions,
                $tokenizedSourceCode,
                $lineNumbersAdded
            );
            sort($lineNumbersAdded);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Inserts line(s) with comments to ignore specific rules.
     * @param array<string> $lines
     * @param array<string> $ruleExclusions
     * @param array<int> $lineNumbersAdded
     */
    private function addRuleExclusions(
        array &$lines,
        int $originalLineNumber,
        array $ruleExclusions,
        TokenizedSourceCode $originalTokenizedSourceCode,
        array &$lineNumbersAdded
    ): void {
        if ($originalLineNumber === 1) {
            $this->ignoreInline($lines, $originalTokenizedSourceCode, $originalLineNumber, $ruleExclusions);
            return;
        }

        if (!$this->canInsertCommentLineBefore($originalLineNumber, $originalTokenizedSourceCode)) {
            $this->insertEnableDisableComments(
                $lines,
                $originalTokenizedSourceCode,
                $originalLineNumber,
                $ruleExclusions,
                $lineNumbersAdded
            );
            return;
        }

        $commentStart = $this->determineCommentStartBeforeLine($originalLineNumber, $originalTokenizedSourceCode);
        $ignoreComment = (new InstructionComment('ignore', $ruleExclusions, ['baseline'], '', $commentStart));
        $this->insertOrMergeComment($lines, $originalLineNumber, $ignoreComment, $lineNumbersAdded);
    }

    /**
     * @param array<string> $lines
     * @param array<string> $ruleExclusions
     */
    private function ignoreInline(
        array &$lines,
        TokenizedSourceCode $tokenizedSourceCode,
        int $lineNumber,
        array $ruleExclusions
    ): void {
        $lastTokenAtFirstLine = $tokenizedSourceCode->getLastTokenAtLineIgnoringWhitespace($lineNumber);
        if ($lastTokenAtFirstLine === null) {
            return;
        }
        $instructionComment = new InstructionComment('ignore', $ruleExclusions);
        $lastTokenContents = rtrim($lastTokenAtFirstLine->getContents(), "\n\r");
        $existingInstruction = $this->ignoreCommentParser->parse($lastTokenContents);
        if ($existingInstruction !== null && $existingInstruction->canMerge($instructionComment)) {
            $existingInstruction->merge($instructionComment);
            $lineContentsWithoutInstructionComment = substr($lines[$lineNumber - 1], 0, -strlen($lastTokenContents));
            $lines[$lineNumber - 1] = $lineContentsWithoutInstructionComment . $existingInstruction->formatAsLine();
            return;
        }
        if (!$lastTokenAtFirstLine->isPartOfString()) {
            $commentFormatted = $instructionComment->formatAsLine();
            $lines[$lineNumber - 1] .= sprintf(' %s', $commentFormatted);
        }
    }

    private function canInsertCommentLineBefore(int $lineNumber, TokenizedSourceCode $tokenizedSourceCode): bool
    {
        // Comment line can be inserted after the last line
        if ($lineNumber > $tokenizedSourceCode->getMaximumEndingLineNumber()) {
            return true;
        }

        $firstTokenAtLineNumber = $tokenizedSourceCode->getFirstTokenEndingAtOrAfterLine($lineNumber);

        return $firstTokenAtLineNumber !== null
            && !$firstTokenAtLineNumber->isPartOfString()
            && !$firstTokenAtLineNumber->isDocComment();
    }

    /**
     * @param array<string> $lines
     * @param array<string> $ruleExclusions
     * @param array<int> $lineNumbersAdded
     */
    private function insertEnableDisableComments(
        array &$lines,
        TokenizedSourceCode $tokenizedSourceCode,
        int $originalLineNumber,
        array $ruleExclusions,
        array &$lineNumbersAdded
    ): void {
        $disableCommentBeforeLineNumber = $originalLineNumber - 1;
        while (!$this->canInsertCommentLineBefore($disableCommentBeforeLineNumber, $tokenizedSourceCode)) {
            $disableCommentBeforeLineNumber--;
        }
        $disableComment = (new InstructionComment('disable', $ruleExclusions));
        $this->insertOrMergeComment($lines, $disableCommentBeforeLineNumber, $disableComment, $lineNumbersAdded);

        $enableCommentBeforeLineNumber = $originalLineNumber + 1;
        while (!$this->canInsertCommentLineBefore($enableCommentBeforeLineNumber, $tokenizedSourceCode)) {
            $enableCommentBeforeLineNumber++;
        }
        $enableComment = (new InstructionComment('enable', $ruleExclusions));
        $this->insertOrMergeComment($lines, $enableCommentBeforeLineNumber, $enableComment, $lineNumbersAdded);
    }

    /**
     * @param array<string> $lines
     * @param array<int> $lineNumbersAdded
     */
    private function insertOrMergeComment(
        array &$lines,
        int $originalLineNumber,
        InstructionComment $instructionComment,
        array &$lineNumbersAdded
    ): void {
        $lineNumber = $this->getActualLineNumber($originalLineNumber, $lineNumbersAdded);
        $existingComment = $this->ignoreCommentParser->parse($lines[$lineNumber - 2]);
        if ($existingComment !== null) {
            if ($existingComment->canMerge($instructionComment)) {
                $existingComment->merge($instructionComment);
                $lines[$lineNumber - 2] = $existingComment->formatAsLine();
                return;
            } elseif ($existingComment->getInstruction() === 'ignore') {
                // We must not place a new instruction directly after an existing ignore instruction
                $lineNumber--;
            }
        }
        $indentation = array_key_exists($lineNumber - 1, $lines)
            ? $this->determineIndentation($lines[$lineNumber - 1])
            : '';
        array_splice($lines, $lineNumber - 1, 0, $indentation . $instructionComment->formatAsLine());
        $lineNumbersAdded[] = $lineNumber;
    }

    private function determineIndentation(string $line): string
    {
        preg_match('`^(?<indent>\\s*)[^\\s]`', $line, $matches);
        if (!is_array($matches) || !array_key_exists('indent', $matches) || !is_string($matches['indent'])) {
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
                return '* ';
            }
            return '';
        }

        return '// ';
    }

    /**
     * @param array<int> $lineNumbersAdded
     */
    private function getActualLineNumber(int $originalLineNumber, array $lineNumbersAdded): int
    {
        $lineNumber = $originalLineNumber;
        foreach ($lineNumbersAdded as $lineNumberAdded) {
            if ($lineNumber >= $lineNumberAdded) {
                $lineNumber++;
            }
        }
        return $lineNumber;
    }
}
