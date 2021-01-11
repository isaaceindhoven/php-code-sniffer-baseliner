<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Util;

use const PHP_EOL;

class OutputWriter
{
    public function writeLine(string $output): void
    {
        $this->write($output . PHP_EOL);
    }

    public function write(string $output): void
    {
        echo $output;
    }
}
