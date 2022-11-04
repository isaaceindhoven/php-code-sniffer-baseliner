<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use function array_key_exists;
use function explode;
use function preg_match;
use function trim;

class InstructionCommentLineParser
{
    private const PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION
        = '`^(?<indentation>\\s*)(?<comment_start>//\\s*)phpcs:(?<instruction>ignore|disable|enable)' .
        '(?<rules> [^-]+)?( --(?<message>.*))?$`';

    public function parse(string $line): ?InstructionComment
    {
        if (
            preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line, $matches) !== 1
            || !array_key_exists('instruction', $matches)
        ) {
            return null;
        }

        $rules = array_key_exists('rules', $matches) ? $this->parseRules($matches['rules']) : [];
        $messages = array_key_exists('message', $matches) ? $this->parseMessages($matches['message']) : [];
        $indentation = array_key_exists('indentation', $matches) ? $matches['indentation'] : '';
        $commentStart = array_key_exists('comment_start', $matches) ? $matches['comment_start'] : '';

        return new InstructionComment($matches['instruction'], $rules, $messages, $indentation, $commentStart);
    }

    /**
     * @param string $commaSeparatedRules
     * @return array<string>
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
     * @return array<string>
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
