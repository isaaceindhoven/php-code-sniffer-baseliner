<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Config;

use ISAAC\CodeSnifferBaseliner\Config\ConfigFileFinder;

class ConfigFileFinderFactory
{
    public static function create(): ConfigFileFinder
    {
        return new ConfigFileFinder();
    }
}
