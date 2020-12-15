<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

use ISAAC\CodeSnifferBaseliner\Filesystem\FileReader;

class FileReaderFactory
{
    public static function create(): FileReader
    {
        return new FileReader();
    }
}
