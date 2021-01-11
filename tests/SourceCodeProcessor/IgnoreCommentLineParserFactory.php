<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\IgnoreCommentLineParser;

class IgnoreCommentLineParserFactory
{
    public static function create(): IgnoreCommentLineParser
    {
        return new IgnoreCommentLineParser();
    }
}
