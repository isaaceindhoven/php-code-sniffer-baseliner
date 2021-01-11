<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\IgnoreCommentLineMerger;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\IgnoreCommentLineParser;

class AddBaselineProcessorFactory
{
    public static function create(
        ?IgnoreCommentLineParser $ignoreCommentLineParser = null,
        ?IgnoreCommentLineMerger $ignoreCommentLineMerger = null
    ): AddBaselineProcessor {
        return new AddBaselineProcessor(
            $ignoreCommentLineParser !== null ? $ignoreCommentLineParser : IgnoreCommentLineParserFactory::create(),
            $ignoreCommentLineMerger !== null ? $ignoreCommentLineMerger : IgnoreCommentLineMergerFactory::create()
        );
    }
}
