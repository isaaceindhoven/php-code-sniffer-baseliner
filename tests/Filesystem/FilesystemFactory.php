<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Filesystem;

use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;

class FilesystemFactory
{
    public static function create(): Filesystem
    {
        return new MemoryFilesystem();
    }
}
