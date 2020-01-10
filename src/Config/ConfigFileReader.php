<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Config;

use DOMDocument;
use RuntimeException;

use function file_get_contents;
use function sprintf;

class ConfigFileReader
{
    public function readConfig(string $configFilename): Config
    {
        $config = new DOMDocument();
        $config->formatOutput = true;
        $config->preserveWhiteSpace = false;
        $configFileContents = file_get_contents($configFilename);
        if ($configFileContents === false) {
            throw new RuntimeException(sprintf('Failed to read \'%s\'.', $configFilename));
        }
        $config->loadXML($configFileContents);
        return new Config($config);
    }
}
