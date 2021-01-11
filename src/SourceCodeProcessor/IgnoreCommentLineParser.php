<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\Exception\ParseException;

use function array_key_exists;
use function explode;
use function is_array;
use function is_string;
use function preg_match;
use function sprintf;
use function trim;

class IgnoreCommentLineParser
{
    private const PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION
        = '`^(?<instruction>\\s*//\\s*phpcs:ignore)(?<rules> [^-]+)?( --(?<message>.*))?$`';

    public function isIgnoreComment(string $line): bool
    {
        return preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line) === 1;
    }

    public function parse(string $line): IgnoreComment
    {
        preg_match(self::PHPCS_IGNORE_COMMENT_REGULAR_EXPRESSION, $line, $matches);
        if (!is_array($matches) || !array_key_exists('instruction', $matches) || !is_string($matches['instruction'])) {
            throw new ParseException(sprintf('Unable to parse line "%s" as an ignore comment.', $line));
        }

        if (array_key_exists('rules', $matches) && is_string($matches['rules'])) {
            $rules = $this->parseRules($matches['rules']);
        } else {
            $rules = [];
        }

        if (array_key_exists('message', $matches) && is_string($matches['message'])) {
            $message = trim($matches['message']);
        } else {
            $message = '';
        }

        return new IgnoreComment($matches['instruction'], $rules, $message);
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
}
