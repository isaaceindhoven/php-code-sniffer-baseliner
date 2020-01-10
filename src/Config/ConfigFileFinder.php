<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Config;

use RuntimeException;

use function file_exists;
use function sprintf;

class ConfigFileFinder
{
    private const CONFIG_FILENAMES = [
        '.phpcs.xml',
        'phpcs.xml',
        '.phpcs.xml.dist',
        'phpcs.xml.dist',
    ];

    public function findConfigFile(string $basePath): string
    {
        foreach (self::CONFIG_FILENAMES as $configFilename) {
            $configFile = sprintf('%s/%s', $basePath, $configFilename);
            if (file_exists($configFile)) {
                return $configFile;
            }
        }

        throw new RuntimeException('No CodeSniffer config file exists in the current directory.');
    }
}
