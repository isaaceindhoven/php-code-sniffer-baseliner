<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

class AddBaselineProcessorTestDataProvider
{
    private const BASIC = [
        <<<'PHP'
<?php
echo 'test';
PHP
        ,
        [2 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const ON_FIRST_LINE = [
        <<<'PHP'
<?php
echo 'test';
PHP
        ,
        [1 => ['Foo.Bar']],
        <<<'PHP'
<?php // phpcs:ignore Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const ON_FIRST_LINE_WITH_MULTIPLE_TOKENS = [
        <<<'PHP'
<?php echo 'test';
PHP
        ,
        [1 => ['Foo.Bar']],
        <<<'PHP'
<?php echo 'test'; // phpcs:ignore Foo.Bar -- baseline
PHP
        ,
    ];
    private const ON_FIRST_LINE_WITH_MULTILINE_STRING = [
        <<<'PHP'
<?php echo 'test
test';
PHP
        ,
        [1 => ['Foo.Bar']],
        <<<'PHP'
<?php echo 'test
test';
PHP
        ,
    ];
    private const MULTIPLE_LINES = [
        <<<'PHP'
<?php
echo 'test';

echo 'test';
PHP
        ,
        [2 => ['Foo.Bar'], 4 => ['Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- baseline
echo 'test';

// phpcs:ignore Baz.Qux -- baseline
echo 'test';
PHP
        ,
    ];
    private const MULTIPLE_LINES_INCLUDING_FIRST_LINE = [
        <<<'PHP'
<?php
echo 'test';
PHP
        ,
        [1 => ['Foo.Bar'], 2 => ['Baz.Qux']],
        <<<'PHP'
<?php // phpcs:ignore Foo.Bar -- baseline
// phpcs:ignore Baz.Qux -- baseline
echo 'test';
PHP
        ,
    ];
    private const MULTIPLE_RULES_PER_LINE = [
        <<<'PHP'
<?php
echo 'test';
PHP
        ,
        [2 => ['Foo.Bar', 'Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore Baz.Qux, Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const INDENTATION = [
        <<<'PHP'
<?php
while (true) {
    echo 'test';
}
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
while (true) {
    // phpcs:ignore Foo.Bar -- baseline
    echo 'test';
}
PHP
        ,
    ];
    private const MERGE_WITH_EXISTING = [
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore Baz.Qux, Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITH_EXISTING_WITH_MESSAGE = [
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- message
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore Baz.Qux, Foo.Bar -- message; baseline
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITH_INDENT = [
        <<<'PHP'
<?php
    // phpcs:ignore Foo.Bar
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
    // phpcs:ignore Baz.Qux, Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITHOUT_RULES = [
        <<<'PHP'
<?php
// phpcs:ignore
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITH_MESSAGE_WITHOUT_SPACING = [
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar --message
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
// phpcs:ignore Baz.Qux, Foo.Bar -- message; baseline
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITH_NO_SPACING_BEFORE = [
        <<<'PHP'
<?php
//phpcs:ignore Foo.Bar
echo 'test';
PHP
        ,
        [3 => ['Baz.Qux']],
        <<<'PHP'
<?php
//phpcs:ignore Baz.Qux, Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const MERGE_WITH_SAME_RULE = [
        <<<'PHP'
<?php
//phpcs:ignore Foo.Bar
echo 'test';
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
//phpcs:ignore Foo.Bar -- baseline
echo 'test';
PHP
        ,
    ];
    private const MULTILINE_SINGLE_QUOTED_STRING = [
        <<<'PHP'
<?php
echo 'test
test';
exit;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo 'test
test';
// phpcs:enable Foo.Bar -- baseline
exit;
PHP
        ,
    ];
    private const MULTILINE_STRING_ON_LAST_LINE = [
        <<<'PHP'
<?php
echo 'test
test';
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo 'test
test';
// phpcs:enable Foo.Bar -- baseline
PHP
        ,
    ];
    private const MULTILINE_DOUBLE_QUOTED_STRING = [
        <<<'PHP'
<?php
echo "test
test";
exit;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo "test
test";
// phpcs:enable Foo.Bar -- baseline
exit;
PHP
        ,
    ];
    private const MULTILINE_STRING_WITH_INTERPOLATION = [
        <<<'PHP'
<?php
$x = 'x';
echo "test $x
test";
exit;
PHP
        ,
        [4 => ['Foo.Bar']],
        <<<'PHP'
<?php
$x = 'x';
// phpcs:disable Foo.Bar -- baseline
echo "test $x
test";
// phpcs:enable Foo.Bar -- baseline
exit;
PHP
        ,
    ];
    private const MULTILINE_STRING_WITH_INDENTATION = [
        <<<'PHP'
<?php
    echo 'test
    test';
    exit;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
    // phpcs:disable Foo.Bar -- baseline
    echo 'test
    test';
    // phpcs:enable Foo.Bar -- baseline
    exit;
PHP
        ,
    ];
    private const MULTILINE_STRING_WITH_MORE_LINES = [
        <<<'PHP'
<?php
echo 'test
test
test
test';
exit;
PHP
        ,
        [4 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo 'test
test
test
test';
// phpcs:enable Foo.Bar -- baseline
exit;
PHP
        ,
    ];
    private const FIRST_LINE_OF_STRING = [
        <<<'PHP'
<?php
echo "test
test";
exit;
PHP
        ,
        [2 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- baseline
echo "test
test";
exit;
PHP
        ,
    ];
    private const FIRST_LINE_OF_STRING_ON_NEW_LINE = [
        <<<'PHP'
<?php
echo
"test
test";
exit;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
echo
// phpcs:ignore Foo.Bar -- baseline
"test
test";
exit;
PHP
        ,
    ];
    private const NOWDOC = [
        <<<'PHP'
<?php
echo <<<'NOWDOC'
test
NOWDOC
;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo <<<'NOWDOC'
test
NOWDOC
// phpcs:enable Foo.Bar -- baseline
;
PHP
        ,
    ];
    private const HEREDOC = [
        <<<'PHP'
<?php
echo <<<HEREDOC
test
HEREDOC
;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo <<<HEREDOC
test
HEREDOC
// phpcs:enable Foo.Bar -- baseline
;
PHP
        ,
    ];
    private const HEREDOC_MORE_LINES = [
        <<<'PHP'
<?php
echo <<<HEREDOC
test
test
HEREDOC
;
PHP
        ,
        [4 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:disable Foo.Bar -- baseline
echo <<<HEREDOC
test
test
HEREDOC
// phpcs:enable Foo.Bar -- baseline
;
PHP
        ,
    ];
    private const SINGLE_LINE_COMMENTS = [
        <<<'PHP'
<?php
// Foo
// Bar
// Baz
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// Foo
// phpcs:ignore Foo.Bar -- baseline
// Bar
// Baz
PHP
        ,
    ];
    private const INSIDE_COMMENT_BLOCK = [
        <<<'PHP'
<?php
/* test
test
test */
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
/* test
phpcs:ignore Foo.Bar -- baseline
test
test */
PHP
        ,
    ];
    private const BETWEEN_COMMENT_BLOCKS = [
        <<<'PHP'
<?php
/* test
test */
/* test
test */
PHP
        ,
        [4 => ['Foo.Bar']],
        <<<'PHP'
<?php
/* test
test */
// phpcs:ignore Foo.Bar -- baseline
/* test
test */
PHP
        ,
    ];
    private const BETWEEN_COMMENT_LINE_AND_COMMENT_BLOCK = [
        <<<'PHP'
<?php
// test
/* test
test */
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
// test
// phpcs:ignore Foo.Bar -- baseline
/* test
test */
PHP
        ,
    ];
    private const DOUBLE_COMMENT_BLOCK = [
        <<<'PHP'
<?php
/* test
test *//* test
test */
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
/* test
phpcs:ignore Foo.Bar -- baseline
test *//* test
test */
PHP
        ,
    ];
    private const ABOVE_SINGLE_COMMENT_BLOCK = [
        <<<'PHP'
<?php
/* test */
exit;
PHP
        ,
        [2 => ['Foo.Bar']],
        <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- baseline
/* test */
exit;
PHP
        ,
    ];
    private const STAR_PREFIX_IN_COMMENT_BLOCK = [
        <<<'PHP'
<?php
/*
 * test
 */
exit;
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
/*
 * @phpcs:ignore Foo.Bar -- baseline
 * test
 */
exit;
PHP
        ,
    ];
    private const STAR_PREFIX_IN_COMMENT_BLOCK_LAST_LINE = [
        <<<'PHP'
<?php
/*
 * test
 */
exit;
PHP
        ,
        [4 => ['Foo.Bar']],
        <<<'PHP'
<?php
/*
 * test
 * @phpcs:ignore Foo.Bar -- baseline
 */
exit;
PHP
        ,
    ];
    private const STAR_PREFIX_IN_DOC_COMMENT = [
        <<<'PHP'
<?php
/**
 * test
 */
PHP
        ,
        [3 => ['Foo.Bar']],
        <<<'PHP'
<?php
/**
 * @phpcs:ignore Foo.Bar -- baseline
 * test
 */
PHP
        ,
    ];

    private const ALL = [
        'basic' => self::BASIC,
        'on first line' => self::ON_FIRST_LINE,
        'on first line with multiple tokens' => self::ON_FIRST_LINE_WITH_MULTIPLE_TOKENS,
        'on first line with multiline string' => self::ON_FIRST_LINE_WITH_MULTILINE_STRING,
        'multiple lines' => self::MULTIPLE_LINES,
        'multiple lines including first line' => self::MULTIPLE_LINES_INCLUDING_FIRST_LINE,
        'multiple rules per line' => self::MULTIPLE_RULES_PER_LINE,
        'indentation' => self::INDENTATION,
        'merge with existing' => self::MERGE_WITH_EXISTING,
        'merge with existing with message' => self::MERGE_WITH_EXISTING_WITH_MESSAGE,
        'merge with indent' => self::MERGE_WITH_INDENT,
        'merge without rule (should not happen)' => self::MERGE_WITHOUT_RULES,
        'merge with message without spacing' => self::MERGE_WITH_MESSAGE_WITHOUT_SPACING,
        'merge with no spacing before' => self::MERGE_WITH_NO_SPACING_BEFORE,
        'merge with same rule (should not happen)' => self::MERGE_WITH_SAME_RULE,
        'multiline single quoted string' => self::MULTILINE_SINGLE_QUOTED_STRING,
        'multiline string on last line' => self::MULTILINE_STRING_ON_LAST_LINE,
        'multiline double quoted string' => self::MULTILINE_DOUBLE_QUOTED_STRING,
        'multiline string with interpolation' => self::MULTILINE_STRING_WITH_INTERPOLATION,
        'multiline string with indentation' => self::MULTILINE_STRING_WITH_INDENTATION,
        'multiline string with more lines' => self::MULTILINE_STRING_WITH_MORE_LINES,
        'first line of string' => self::FIRST_LINE_OF_STRING,
        'first line of string on new line' => self::FIRST_LINE_OF_STRING_ON_NEW_LINE,
        'nowdoc' => self::NOWDOC,
        'heredoc' => self::HEREDOC,
        'heredoc more lines' => self::HEREDOC_MORE_LINES,
        'single line comments' => self::SINGLE_LINE_COMMENTS,
        'inside comment block' => self::INSIDE_COMMENT_BLOCK,
        'between comment blocks' => self::BETWEEN_COMMENT_BLOCKS,
        'between comment line and comment block' => self::BETWEEN_COMMENT_LINE_AND_COMMENT_BLOCK,
        'double comment block' => self::DOUBLE_COMMENT_BLOCK,
        'above single comment block' => self::ABOVE_SINGLE_COMMENT_BLOCK,
        'star prefix in comment block' => self::STAR_PREFIX_IN_COMMENT_BLOCK,
        'star prefix in comment block at last line' => self::STAR_PREFIX_IN_COMMENT_BLOCK_LAST_LINE,
        'star prefix in doc comment' => self::STAR_PREFIX_IN_DOC_COMMENT,
    ];

    /**
     * @return mixed[]
     */
    public static function provide(): array
    {
        return self::ALL;
    }
}
