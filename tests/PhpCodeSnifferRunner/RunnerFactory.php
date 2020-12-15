<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;

class RunnerFactory
{
    public static function create(): Runner
    {
        return new Runner();
    }
}
