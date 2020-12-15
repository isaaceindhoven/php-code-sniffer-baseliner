<?php

namespace ISAAC\CodeSnifferBaseliner\Filesystem;

interface Filesystem
{
    public function readContents(string $filename): string;

    public function replaceContents(string $filename, string $contents): void;

    public function isFile(string $filename): bool;

    public function isDirectory(string $filename): bool;

    public function getFilesInDirectory(string $directoryPath): iterable;
}
