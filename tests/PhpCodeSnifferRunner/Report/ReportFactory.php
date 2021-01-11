<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\FileReport;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Totals;

use function array_map;
use function array_sum;

class ReportFactory
{
    /**
     * @param FileReport[] $fileReportsByFilenameOrDefault
     */
    public static function create(
        ?Totals $totals = null,
        ?array $fileReportsByFilenameOrDefault = null
    ): Report {
        $fileReportsByFilename = $fileReportsByFilenameOrDefault !== null ? $fileReportsByFilenameOrDefault : [];
        return new Report(
            $totals !== null ? $totals : TotalsFactory::create(
                (int) array_sum(array_map(static function (FileReport $fileReport): int {
                    return $fileReport->getErrorCount();
                }, $fileReportsByFilename)),
                (int) array_sum(array_map(static function (FileReport $fileReport): int {
                    return $fileReport->getWarningCount();
                }, $fileReportsByFilename)),
            ),
            $fileReportsByFilename
        );
    }

    /**
     * @param FileReport[] $fileReportsByFilename
     */
    public static function createWithFileReportsByFilename(array $fileReportsByFilename): Report
    {
        return self::create(null, $fileReportsByFilename);
    }
}
