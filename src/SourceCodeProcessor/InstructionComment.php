<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\Exception\MergeException;

use function array_merge;
use function array_unique;
use function count;
use function implode;
use function sort;
use function sprintf;

class InstructionComment
{
    /**
     * @var string
     */
    private $indentation;
    /**
     * @var string
     */
    private $commentStart;
    /**
     * @var string
     */
    private $instruction;
    /**
     * @var array<string>
     */
    private $rules;
    /**
     * @var array<string>
     */
    private $messages;

    /**
     * @param array<string> $rules
     * @param array<string> $messages
     */
    public function __construct(
        string $instruction,
        array $rules,
        array $messages = ['baseline'],
        string $indentation = '',
        string $commentStart = '// '
    ) {
        $this->indentation = $indentation;
        $this->commentStart = $commentStart;
        $this->instruction = $instruction;
        sort($rules);
        $this->rules = array_unique($rules);
        $this->messages = $messages;
    }

    public function getInstruction(): string
    {
        return $this->instruction;
    }

    public function hasRules(): bool
    {
        return count($this->rules) > 0;
    }

    public function canMerge(InstructionComment $instructionComment): bool
    {
        return $this->instruction === $instructionComment->instruction;
    }

    public function merge(InstructionComment $instructionComment): void
    {
        if ($this->instruction !== $instructionComment->instruction) {
            throw new MergeException('Unable to merge instruction comments with different instructions.');
        }
        if (count($this->rules) > 0) {
            $this->rules = array_unique(array_merge($this->rules, $instructionComment->rules));
            sort($this->rules);
            $this->messages = array_unique(array_merge($this->messages, $instructionComment->messages));
        }
    }

    public function formatAsLine(): string
    {
        $line = sprintf('%s%sphpcs:%s', $this->indentation, $this->commentStart, $this->instruction);

        if (count($this->rules) > 0) {
            $line .= sprintf(' %s', implode(', ', $this->rules));
        }

        if (count($this->messages) > 0) {
            $line .= sprintf(' -- %s', implode('; ', $this->messages));
        }

        return $line;
    }
}
