<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;

class IgnoreCommentLineParserFactory
{
    public static function create(): InstructionCommentLineParser
    {
        return new InstructionCommentLineParser();
    }
}
