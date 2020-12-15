<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;

class AddBaselineProcessorFactory
{
    public static function create(): AddBaselineProcessor
    {
        return new AddBaselineProcessor();
    }
}
