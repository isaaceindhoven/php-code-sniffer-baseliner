<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report;

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

    public function getSource(): string
    {
        return $this->source;
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

    public function isError(): bool
    {
        return $this->type === 'ERROR';
    }

    public function isWarning(): bool
    {
        return $this->type === 'WARNING';
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
