<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;

class AddBaselineProcessorFactory
{
    public static function create(
        ?InstructionCommentLineParser $ignoreCommentLineParser = null
    ): AddBaselineProcessor {
        return new AddBaselineProcessor(
            $ignoreCommentLineParser !== null ? $ignoreCommentLineParser : IgnoreCommentLineParserFactory::create()
        );
    }
}
