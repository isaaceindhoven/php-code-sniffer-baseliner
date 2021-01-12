<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use function array_key_exists;
use function explode;
use function is_array;
use function is_string;
use function preg_match;
use function trim;

class InstructionCommentLineParser
{
    private const PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION
        = '`^(?<indentation>\\s*)(?<comment_start>//\\s*)phpcs:(?<instruction>ignore|disable|enable)' .
        '(?<rules> [^-]+)?( --(?<message>.*))?$`';

    public function parse(string $line, string $instruction): ?InstructionComment
    {
        if (
            preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line, $matches) !== 1
            || !is_array($matches)
            || !array_key_exists('instruction', $matches)
            || $matches['instruction'] !== $instruction
        ) {
            return null;
        }

        $rules = array_key_exists('rules', $matches) && is_string($matches['rules'])
            ? $this->parseRules($matches['rules'])
            : [];
        $messages = array_key_exists('message', $matches) && is_string($matches['message'])
            ? $this->parseMessages($matches['message'])
            : [];
        $indentation = array_key_exists('indentation', $matches) && is_string($matches['indentation'])
            ? $matches['indentation']
            : '';
        $commentStart = array_key_exists('comment_start', $matches) && is_string($matches['comment_start'])
            ? $matches['comment_start']
            : '';

        return new InstructionComment($matches['instruction'], $rules, $messages, $indentation, $commentStart);
    }

    /**
     * @param string $commaSeparatedRules
     * @return string[]
     */
    private function parseRules(string $commaSeparatedRules): array
    {
        $rules = [];
        foreach (explode(',', $commaSeparatedRules) as $rule) {
            $trimmedRule = trim($rule);
            if ($trimmedRule === '') {
                continue;
            }
            $rules[] = $trimmedRule;
        }
        return $rules;
    }

    /**
     * @param string $semiColonSeparatedMessages
     * @return string[]
     */
    private function parseMessages(string $semiColonSeparatedMessages): array
    {
        $messages = [];
        foreach (explode(';', $semiColonSeparatedMessages) as $message) {
            $trimmedMessage = trim($message);
            if ($trimmedMessage === '') {
                continue;
            }
            $messages[] = $trimmedMessage;
        }
        return $messages;
    }
}
