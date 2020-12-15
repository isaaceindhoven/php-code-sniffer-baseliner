<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;

class FileContentsManipulatorFactory
{
    public static function create(): AddBaselineProcessor
    {
        return new AddBaselineProcessor();
    }
}
