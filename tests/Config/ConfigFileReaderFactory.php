<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Config;

use ISAAC\CodeSnifferBaseliner\Config\ConfigFileReader;

class ConfigFileReaderFactory
{
    public static function create(): ConfigFileReader
    {
        return new ConfigFileReader();
    }
}
