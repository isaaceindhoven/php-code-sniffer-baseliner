<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpTokenizer;

use function in_array;
use function json_encode;
use function sprintf;
use function substr;
use function substr_count;
use function token_name;

use const PHP_EOL;
use const T_COMMENT;
use const T_CONSTANT_ENCAPSED_STRING;
use const T_DOC_COMMENT;
use const T_ENCAPSED_AND_WHITESPACE;
use const T_START_HEREDOC;
use const T_WHITESPACE;

class Token
{
    /**
     * @var int
     */
    private int $type;
    /**
     * @var string
     */
    private string $contents;
    /**
     * @var int
     */
    private int $startingLineNumber;

    public function __construct(int $type, string $contents, int $startingLineNumber)
    {
        $this->type = $type;
        $this->contents = $contents;
        $this->startingLineNumber = $startingLineNumber;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getStartingLineNumber(): int
    {
        return $this->startingLineNumber;
    }

    public function getEndingLineNumber(): int
    {
        return $this->startingLineNumber + substr_count($this->contents, PHP_EOL);
    }

    public function isPartOfString(): bool
    {
        return in_array($this->type, [T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC, T_CONSTANT_ENCAPSED_STRING], true);
    }

    public function isMultiLineComment(): bool
    {
        return in_array($this->type, [T_COMMENT, T_DOC_COMMENT], true)
            && $this->getEndingLineNumber() > $this->getStartingLineNumber()
            && substr($this->contents, 0, 2) === '/*';
    }

    public function isDocComment(): bool
    {
        return $this->type === T_DOC_COMMENT;
    }

    public function isWhiteSpace(): bool
    {
        return $this->type === T_WHITESPACE;
    }

    public function __toString(): string
    {
        return sprintf(
            '%d-%d: %s (type: %s)',
            $this->startingLineNumber,
            $this->getEndingLineNumber(),
            json_encode($this->contents),
            token_name($this->type)
        );
    }
}
