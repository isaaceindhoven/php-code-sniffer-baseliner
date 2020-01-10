<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Baseline;

use function array_key_exists;
use function in_array;

class Baseline
{
    /**
     * @var string[][]
     */
    private $excludedFilesByRule;

    /**
     * @param string[][] $excludedFilesByRule
     */
    public function __construct(array $excludedFilesByRule)
    {
        $this->excludedFilesByRule = $excludedFilesByRule;
    }

    /**
     * @return string[]
     */
    public function getFilesForRule(string $rule): array
    {
        if (!array_key_exists($rule, $this->excludedFilesByRule)) {
            return [];
        }
        return $this->excludedFilesByRule[$rule];
    }

    /**
     * @return string[][]
     */
    public function getExcludedFilesByRule(): array
    {
        return $this->excludedFilesByRule;
    }

    public function containsExclusion(string $rule, string $file): bool
    {
        return in_array($file, $this->getFilesForRule($rule), true);
    }
}
