<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Baseline;

use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\FileReportFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\MessageFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\ReportFactory;
use PHPUnit\Framework\TestCase;

class BaselineFactoryTest extends TestCase
{
    /**
     * @var BaselineFactory
     */
    private BaselineFactory $baselineFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baselineFactory = new BaselineFactory();
    }

    public function testCreateFromReport(): void
    {
        $report = ReportFactory::createWithFileReportsByFilename([
            'test.php' => FileReportFactory::createWithMessages([
                MessageFactory::createWithSourceAndLine('Foo.Bar', 123),
            ]),
        ]);

        $baseline = $this->baselineFactory->createFromReport($report);

        $violatedRulesByFileAndLineNumber = $baseline->getViolatedRulesByFileAndLineNumber();
        self::assertCount(1, $violatedRulesByFileAndLineNumber);
        self::assertArrayHasKey('test.php', $violatedRulesByFileAndLineNumber);
        self::assertCount(1, $violatedRulesByFileAndLineNumber['test.php']);
        self::assertArrayHasKey(123, $violatedRulesByFileAndLineNumber['test.php']);
        self::assertCount(1, $violatedRulesByFileAndLineNumber['test.php'][123]);
        self::assertArrayHasKey(0, $violatedRulesByFileAndLineNumber['test.php'][123]);
        self::assertSame('Foo.Bar', $violatedRulesByFileAndLineNumber['test.php'][123][0]);
    }
}
