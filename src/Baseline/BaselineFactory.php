<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Baseline;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;

use function array_map;

class BaselineFactory
{
    public function createFromReport(Report $report): Baseline
    {
        $fileReportsByFilename = $report->getFileReportsByFilename();
        return new Baseline(array_map(function (FileReport $fileReport): array {
            return $this->groupSourcesByLine($fileReport);
        }, $fileReportsByFilename));
    }

    /**
     * @return array<array<string>>
     */
    private function groupSourcesByLine(FileReport $fileReport): array
    {
        $sourcesByLine = [];
        foreach ($fileReport->getMessages() as $message) {
            $sourcesByLine[$message->getLine()][] = $message->getSource();
        }
        return $sourcesByLine;
    }
}
