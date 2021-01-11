<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Filesystem;

use function file_get_contents;
use function file_put_contents;
use function sprintf;

class NativeFilesystem implements Filesystem
{
    public function readContents(string $filename): string
    {
        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new FilesystemException(sprintf('Unable to read contents from file "%s".', $filename));
        }
        return $contents;
    }

    public function replaceContents(string $filename, string $contents): void
    {
        file_put_contents($filename, $contents);
    }
}
