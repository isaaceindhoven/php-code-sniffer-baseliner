<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Filesystem;

use function file_get_contents;
use function file_put_contents;
use function in_array;
use function opendir;
use function readdir;

class NativeFilesystem implements Filesystem
{
    public function readContents(string $filename): string
    {
        return file_get_contents($filename);
    }

    public function replaceContents(string $filename, string $contents): void
    {
        file_put_contents($filename, $contents);
    }

    public function isFile(string $filename): bool
    {
        return is_file($filename);
    }

    public function isDirectory(string $filename): bool
    {
        return is_dir($filename);
    }

    public function getFilesInDirectory(string $directoryPath): iterable
    {
        $directoryHandle = opendir($directoryPath);
        do {
            $filename = readdir($directoryHandle);
            if (is_string($filename) && !in_array($filename, ['.', '..'], true)) {
                yield $filename;
            }
        } while ($filename !== false);
    }
}
