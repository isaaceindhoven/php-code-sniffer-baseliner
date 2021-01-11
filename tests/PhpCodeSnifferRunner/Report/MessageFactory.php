<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Message;

class MessageFactory
{
    public static function create(
        ?string $message = null,
        ?string $source = null,
        ?int $severity = null,
        ?bool $fixable = null,
        ?string $type = null,
        ?int $line = null,
        ?int $column = null
    ): Message {
        return new Message(
            $message !== null ? $message : '',
            $source !== null ? $source : '',
            $severity !== null ? $severity : 0,
            $fixable !== null ? $fixable : false,
            $type !== null ? $type : 'ERROR',
            $line !== null ? $line : 0,
            $column !== null ? $column : 0
        );
    }

    public static function createWithSourceAndLine(string $source, int $line): Message
    {
        return self::create(null, $source, null, null, null, $line);
    }
}
