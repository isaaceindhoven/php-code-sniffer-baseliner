<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Report\Report;
use RuntimeException;

use function array_key_exists;
use function escapeshellarg;
use function exec;
use function sprintf;
use function strpos;

class Runner
{
    public function run(string $basePath): Report
    {
        $cliCommand = sprintf($basePath.'/vendor/bin/phpcs -q --report=json');

        exec($cliCommand, $output);

        if (!array_key_exists(0, $output)) {
            throw new RuntimeException(sprintf('The CLI command \'%s\' did not produce any output.', $cliCommand));
        }
        if (strpos($output[0], 'ERROR:') === 0) {
            throw new RuntimeException($output[0]);
        }

        return (new ReportDeserializer())->deserializeReport($output[0]);
    }
}
