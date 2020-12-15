<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\File;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use PHPUnit\Framework\TestCase;

class AddRuleExclusionsToSourceCodeProcessorTest extends TestCase
{
    /**
     * @var AddBaselineProcessor
     */
    private $addRuleExclusionsToSourceCodeProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addRuleExclusionsToSourceCodeProcessor = new AddBaselineProcessor();
    }

    /**
     * @param string[][] $ruleExclusionsByLineNumber
     * @dataProvider addRuleExclusionsByLineNumberDataProvider
     */
    public function testAddRuleExclusionsByLineNumber(
        string $fileContents,
        array $ruleExclusionsByLineNumber,
        string $expectedModifiedFileContents
    ): void {
        $modifiedFileContents = $this->addRuleExclusionsToSourceCodeProcessor->addRuleExclusionsByLineNumber(
            $fileContents,
            $ruleExclusionsByLineNumber
        );

        self::assertSame($expectedModifiedFileContents, $modifiedFileContents);
    }

    public function addRuleExclusionsByLineNumberDataProvider(): array
    {
        return [
            'basic' => [
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
            ],
            'on first line' => [
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
            ],
            'on first line with multiple tokens' => [
                <<<'PHP'
<?php echo 'test';
PHP
                ,
                [1 => ['Foo.Bar']],
                <<<'PHP'
<?php echo 'test'; // phpcs:ignore Foo.Bar -- baseline
PHP
                ,
            ],
            // TODO: decide what to do when a multiline string starts on the first line
            'on first line with multiline string' => [
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
            ],
            'multiple lines' => [
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
            ],
            'multiple lines including first line' => [
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
            ],
            'multiple rules per line' => [
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
            ],
            'indentation' => [
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
            ],
            'merge with existing' => [
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
            ],
            'merge with existing with message' => [
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
            ],
            'merge with indent' => [
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
            ],
            'merge without rule (should not happen)' => [
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
            ],
            'merge with message without spacing' => [
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
            ],
            'merge with no spacing before' => [
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
            ],
            'merge with same rule (should not happen)' => [
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
            ],
            'multiline single quoted string' => [
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
            ],
            'multiline string on last line' => [
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
            ],
            'multiline double quoted string' => [
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
            ],
            'multiline double quoted string with interpolation' => [
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
            ],
            'multiline string with indentation' => [
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
            ],
            'multiline string with more lines' => [
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
            ],
            'first line of string' => [
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
            ],
            'first line of string on new line' => [
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
            ],
            'nowdoc' => [
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
            ],
            'heredoc' => [
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
            ],
            'heredoc more lines' => [
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
            ],
            'single line comments' => [
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
            ],
            'inside comment block' => [
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
            ],
            'between comment blocks' => [
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
            ],
            'between comment line and comment block' => [
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
            ],
            'double comment block' => [
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
            ],
            'above single comment block' => [
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
            ],
            'star prefix in comment block' => [
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
            ],
            'star prefix in comment block at last line' => [
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
            ],
        ];
    }
}
