<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Util;

use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;

class OutputWriterFactory
{
    public static function create(): OutputWriter
    {
        return new OutputWriter();
    }
}
