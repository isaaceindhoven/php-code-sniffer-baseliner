<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Filesystem;

interface Filesystem
{
    /**
     * @throws FilesystemException
     */
    public function readContents(string $filename): string;

    public function replaceContents(string $filename, string $contents): void;
}
