<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\SourceCodeProcessor;

class IgnoreCommentLineMerger
{
    /**
     * @var IgnoreCommentLineParser
     */
    private $ignoreCommentLineParser;

    public function __construct(IgnoreCommentLineParser $ignoreCommentLineParser)
    {
        $this->ignoreCommentLineParser = $ignoreCommentLineParser;
    }

    /**
     * @param string[] $ruleExclusions
     */
    public function mergeIgnoreCommentLine(string $line, array $ruleExclusions): string
    {
        $ignoreComment = $this->ignoreCommentLineParser->parse($line);
        if (!$ignoreComment->hasRules()) {
            // Do not modify line if no rules are specified
            return $line;
        }

        $ignoreComment->mergeRules($ruleExclusions);
        $ignoreComment->appendMessage('baseline');

        return $ignoreComment->formatAsLine();
    }
}
