<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Baseline;

class Baseline
{
    /**
     * @var string[][][]
     */
    private $violatedRulesByFileAndLineNumber;

    /**
     * @param string[][][] $violatedRulesByFileAndLineNumber
     */
    public function __construct(array $violatedRulesByFileAndLineNumber)
    {
        $this->violatedRulesByFileAndLineNumber = $violatedRulesByFileAndLineNumber;
    }

    /**
     * @return string[][][]
     */
    public function getViolatedRulesByFileAndLineNumber(): array
    {
        return $this->violatedRulesByFileAndLineNumber;
    }
}
