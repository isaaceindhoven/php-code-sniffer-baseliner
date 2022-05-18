<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report;

class Totals
{
    /**
     * @var int
     */
    private int $errors;
    /**
     * @var int
     */
    private int $warnings;
    /**
     * @var int
     */
    private int $fixable;

    public function __construct(int $errors, int $warnings, int $fixable)
    {
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->fixable = $fixable;
    }

    public function getErrors(): int
    {
        return $this->errors;
    }

    public function getWarnings(): int
    {
        return $this->warnings;
    }

    public function getFixable(): int
    {
        return $this->fixable;
    }
}
