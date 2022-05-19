<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Message;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Totals;
use ISAAC\CodeSnifferBaseliner\Util\PropertyAccessor;
use RuntimeException;

use function array_map;
use function get_object_vars;
use function gettype;
use function is_object;
use function json_decode;
use function sprintf;

class ReportDeserializer
{
    public function deserializeReport(string $jsonSerializedReport): Report
    {
        $normalizedReport = json_decode($jsonSerializedReport, false);
        if (!is_object($normalizedReport)) {
            throw new RuntimeException(sprintf('PHP_CodeSniffer returned invalid JSON: %s', $jsonSerializedReport));
        }

        return new Report(
            $this->denormalizeTotals(PropertyAccessor::getObjectProperty($normalizedReport, 'totals', 'report')),
            $this->denormalizeFileReports(PropertyAccessor::getObjectProperty($normalizedReport, 'files', 'report'))
        );
    }

    private function denormalizeTotals(object $normalizedTotals): Totals
    {
        return new Totals(
            PropertyAccessor::getIntegerProperty($normalizedTotals, 'errors', 'totals'),
            PropertyAccessor::getIntegerProperty($normalizedTotals, 'errors', 'totals'),
            PropertyAccessor::getIntegerProperty($normalizedTotals, 'errors', 'totals')
        );
    }

    /**
     * @return array<FileReport>
     */
    private function denormalizeFileReports(object $normalizedFiles): array
    {
        return array_map(function ($normalizedFileReport): FileReport {
            if (!is_object($normalizedFileReport)) {
                throw new RuntimeException(
                    sprintf('Expected file to be an object, got %s.', gettype($normalizedFileReport))
                );
            }
            return new FileReport(
                PropertyAccessor::getIntegerProperty($normalizedFileReport, 'errors', 'file'),
                PropertyAccessor::getIntegerProperty($normalizedFileReport, 'warnings', 'file'),
                $this->denormalizeMessages(
                    PropertyAccessor::getArrayProperty($normalizedFileReport, 'messages', 'file')
                )
            );
        }, get_object_vars($normalizedFiles));
    }

    /**
     * @param array<mixed> $normalizedMessages
     * @return array<Message>
     */
    private function denormalizeMessages(array $normalizedMessages): array
    {
        return array_map(static function ($normalizedMessage): Message {
            if (!is_object($normalizedMessage)) {
                throw new RuntimeException(
                    sprintf('Expected message to be an object, got %s.', gettype($normalizedMessage))
                );
            }
            return new Message(
                PropertyAccessor::getStringProperty($normalizedMessage, 'message', 'message'),
                PropertyAccessor::getStringProperty($normalizedMessage, 'source', 'message'),
                PropertyAccessor::getIntegerProperty($normalizedMessage, 'severity', 'message'),
                PropertyAccessor::getBooleanProperty($normalizedMessage, 'fixable', 'message'),
                PropertyAccessor::getStringProperty($normalizedMessage, 'type', 'message'),
                PropertyAccessor::getIntegerProperty($normalizedMessage, 'line', 'message'),
                PropertyAccessor::getIntegerProperty($normalizedMessage, 'column', 'message')
            );
        }, $normalizedMessages);
    }
}
