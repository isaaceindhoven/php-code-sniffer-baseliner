<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

use ISAAC\CodeSnifferBaseliner\Filesystem\FileWriter;

class FileWriterFactory
{
    public static function create(): FileWriter
    {
        return new FileWriter();
    }
}
