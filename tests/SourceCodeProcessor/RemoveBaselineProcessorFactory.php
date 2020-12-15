<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\RemoveBaselineProcessor;

class RemoveBaselineProcessorFactory
{
    public static function create(): RemoveBaselineProcessor
    {
        return new RemoveBaselineProcessor();
    }
}
