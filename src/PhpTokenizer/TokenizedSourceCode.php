<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpTokenizer;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function implode;
use function max;

use const PHP_EOL;

/**
 * @implements IteratorAggregate<Token>
 */
class TokenizedSourceCode implements IteratorAggregate
{
    /**
     * @var Token[][]
     */
    private $tokensByStartingLineNumber = [];
    /**
     * @var array<int, Token[]>
     */
    private $tokensByEndingLineNumber = [];

    /**
     * @param Token[] $tokens
     */
    public function __construct(array $tokens)
    {
        foreach ($tokens as $token) {
            $this->tokensByStartingLineNumber[$token->getStartingLineNumber()][] = $token;
            $this->tokensByEndingLineNumber[$token->getEndingLineNumber()][] = $token;
        }
    }

    public function getFirstTokenEndingAtOrAfterLine(int $lineNumber): ?Token
    {
        $tokenEndingLineNumber = $lineNumber;
        while (
            !array_key_exists($tokenEndingLineNumber, $this->tokensByEndingLineNumber)
            && $tokenEndingLineNumber <= $this->getMaximumEndingLineNumber()
        ) {
            $tokenEndingLineNumber++;
        }

        if (!array_key_exists($tokenEndingLineNumber, $this->tokensByEndingLineNumber)) {
            return null;
        }

        return $this->tokensByEndingLineNumber[$tokenEndingLineNumber][0];
    }

    public function getLastTokenAtLineIgnoringWhitespace(int $lineNumber): ?Token
    {
        $tokenStartingLineNumber = $lineNumber;
        while (
            !array_key_exists($tokenStartingLineNumber, $this->tokensByStartingLineNumber)
            && $tokenStartingLineNumber > 1
        ) {
            $tokenStartingLineNumber--;
        }

        if (!array_key_exists($tokenStartingLineNumber, $this->tokensByStartingLineNumber)) {
            return null;
        }

        $lastTokenIndex = count($this->tokensByStartingLineNumber[$tokenStartingLineNumber]) - 1;
        while (
            $this->tokensByStartingLineNumber[$tokenStartingLineNumber][$lastTokenIndex]->isWhiteSpace()
            && $lastTokenIndex > 0
        ) {
            $lastTokenIndex--;
        }
        return $this->tokensByStartingLineNumber[$tokenStartingLineNumber][$lastTokenIndex];
    }

    /**
     * @return Iterator<Token>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator(array_merge(...$this->tokensByStartingLineNumber));
    }

    public function __toString(): string
    {
        return implode(PHP_EOL, array_map(static function (Token $token): string {
            return (string) $token;
        }, array_merge(...$this->tokensByStartingLineNumber)));
    }

    public function getMaximumEndingLineNumber(): int
    {
        $endingLineNumbers = array_keys($this->tokensByEndingLineNumber);
        if (count($endingLineNumbers) === 0) {
            return 0;
        }
        return max($endingLineNumbers);
    }
}
