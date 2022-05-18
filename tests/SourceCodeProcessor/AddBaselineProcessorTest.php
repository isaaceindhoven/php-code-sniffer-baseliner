<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use PHPUnit\Framework\TestCase;

class AddBaselineProcessorTest extends TestCase
{
    /**
     * @var AddBaselineProcessor
     */
    private AddBaselineProcessor $addBaselineProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addBaselineProcessor = AddBaselineProcessorFactory::create();
    }

    /**
     * @param array<array<string>> $ruleExclusionsByLineNumber
     * @dataProvider \ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor\AddBaselineProcessorTestDataProvider::provide
     */
    public function testAddRuleExclusionsByLineNumber(
        string $fileContents,
        array $ruleExclusionsByLineNumber,
        string $expectedModifiedFileContents
    ): void {
        $modifiedFileContents = $this->addBaselineProcessor->addRuleExclusionsByLineNumber(
            $fileContents,
            $ruleExclusionsByLineNumber
        );

        self::assertSame($expectedModifiedFileContents, $modifiedFileContents);
    }
}
