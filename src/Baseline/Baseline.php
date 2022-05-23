<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Baseline;

class Baseline
{
    /**
     * @var array<array<array<string>>>
     */
    private $violatedRulesByFileAndLineNumber;

    /**
     * @param array<array<array<string>>> $violatedRulesByFileAndLineNumber
     */
    public function __construct(array $violatedRulesByFileAndLineNumber)
    {
        $this->violatedRulesByFileAndLineNumber = $violatedRulesByFileAndLineNumber;
    }

    /**
     * @return array<array<array<string>>>
     */
    public function getViolatedRulesByFileAndLineNumber(): array
    {
        return $this->violatedRulesByFileAndLineNumber;
    }
}
