<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Config;

use function file_put_contents;

class ConfigFileWriter
{
    public function writeConfig(Config $config, string $configFilename): void
    {
        file_put_contents($configFilename, $config->toXml());
    }
}
