<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;

use function array_filter;
use function array_keys;
use function array_map;
use function array_unique;
use function sprintf;
use function strlen;
use function strpos;
use function substr;

class MemoryFilesystem implements Filesystem
{
    private $fileContentsByFilename = [];

    public function readContents(string $filename): string
    {
        return $this->fileContentsByFilename[$filename];
    }

    public function replaceContents(string $filename, string $contents): void
    {
        $this->fileContentsByFilename[$filename] = $contents;
    }

    public function isFile(string $filename): bool
    {
        return array_key_exists($filename, $this->fileContentsByFilename);
    }

    public function isDirectory(string $filename): bool
    {
        foreach (array_keys($this->fileContentsByFilename) as $filePath) {
            if (substr($filePath, 0, strlen($filename) + 1) === sprintf('%s/', $filename)) {
                return true;
            }
        }

        return false;
    }

    public function getFilesInDirectory(string $directoryPath): iterable
    {
        $filenamePrefix = sprintf('%s/', $directoryPath);
        return array_unique(array_map(function (string $filePath) use ($filenamePrefix): string {
            $filePathWithoutPrefix = substr($filePath, strlen($filenamePrefix));
            return substr($filePathWithoutPrefix, 0, strpos($filePathWithoutPrefix, '/'));
        }, array_filter($this->fileContentsByFilename, function (string $filePath) use ($filenamePrefix): bool {
            return substr($filePath, 0, strlen($filenamePrefix)) === $filenamePrefix;
        })));
    }
}
