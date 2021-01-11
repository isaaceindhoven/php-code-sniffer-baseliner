<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Totals;

class TotalsFactory
{
    public static function create(?int $errors = null, ?int $warnings = null, ?int $fixable = null): Totals
    {
        return new Totals(
            $errors !== null ? $errors : 0,
            $warnings !== null ? $warnings : 0,
            $fixable !== null ? $fixable : 0
        );
    }
}
