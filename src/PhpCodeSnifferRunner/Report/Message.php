<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report;

use function explode;
use function implode;
use function strpos;

class Message
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $source;
    /**
     * @var int
     */
    private $severity;
    /**
     * @var bool
     */
    private $fixable;
    /**
     * @var string
     */
    private $type;
    /**
     * @var int
     */
    private $line;
    /**
     * @var int
     */
    private $column;

    public function __construct(
        string $message,
        string $source,
        int $severity,
        bool $fixable,
        string $type,
        int $line,
        int $column
    ) {
        $this->message = $message;
        $this->source = $source;
        $this->severity = $severity;
        $this->fixable = $fixable;
        $this->type = $type;
        $this->line = $line;
        $this->column = $column;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRuleName(): string
    {
        $ruleNameParts = [];
        foreach (explode('.', $this->source) as $ruleNamePart) {
            if (strpos($ruleNamePart, '\\') !== false) {
                break;
            }
            $ruleNameParts[] = $ruleNamePart;
        }
        return implode('.', $ruleNameParts);
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }

    public function isFixable(): bool
    {
        return $this->fixable;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}
