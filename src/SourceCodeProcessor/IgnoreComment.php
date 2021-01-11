<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use function array_merge;
use function array_unique;
use function count;
use function implode;
use function sort;
use function sprintf;

class IgnoreComment
{
    /**
     * @var string
     */
    private $instruction;
    /**
     * @var string[]
     */
    private $rules;
    /**
     * @var string
     */
    private $message;

    /**
     * @param string[] $rules
     */
    public function __construct(string $instruction, array $rules, string $message)
    {
        $this->instruction = $instruction;
        sort($rules);
        $this->rules = array_unique($rules);
        $this->message = $message;
    }

    public function hasRules(): bool
    {
        return count($this->rules) > 0;
    }

    /**
     * @param string[] $ruleExclusions
     */
    public function mergeRules(array $ruleExclusions): void
    {
        $this->rules = array_unique(array_merge($this->rules, $ruleExclusions));
        sort($this->rules);
    }

    public function appendMessage(string $message): void
    {
        $this->message .= $this->message === '' ? $message : sprintf('; %s', $message);
    }

    public function formatAsLine(): string
    {
        $line = $this->instruction;

        if (count($this->rules) > 0) {
            $line .= sprintf(' %s', implode(', ', $this->rules));
        }

        if ($this->message !== '') {
            $line .= sprintf(' -- %s', $this->message);
        }

        return $line;
    }
}
