<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Message;

use function array_fill;
use function array_filter;

class FileReportFactory
{
    /**
     * @param Message[] $messages
     */
    public static function create(
        ?int $errorCount = null,
        ?int $warningCount = null,
        ?array $messages = null
    ): FileReport {
        return new FileReport(
            $errorCount !== null ? $errorCount : count(array_filter($messages, function (Message $message): bool {
                return $message->isError();
            })),
            $warningCount !== null ? $warningCount : count(array_filter($messages, function (Message $message): bool {
                return $message->isWarning();
            })),
            $messages !== null ? $messages : []
        );
    }

    /**
     * @param Message[] $messages
     */
    public static function createWithMessages(?array $messages = null): FileReport
    {
        return self::create(null, null, $messages);
    }
}
