<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\IgnoreCommentLineMerger;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\IgnoreCommentLineParser;

class IgnoreCommentLineMergerFactory
{
    public static function create(?IgnoreCommentLineParser $ignoreCommentLineParser = null): IgnoreCommentLineMerger
    {
        return new IgnoreCommentLineMerger(
            $ignoreCommentLineParser !== null ? $ignoreCommentLineParser : IgnoreCommentLineParserFactory::create()
        );
    }
}
