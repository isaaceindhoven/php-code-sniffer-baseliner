<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\BaselineCreator;
use ISAAC\CodeSnifferBaseliner\BasePathFinder;
use ISAAC\CodeSnifferBaseliner\Filesystem\FileReader;
use ISAAC\CodeSnifferBaseliner\Filesystem\NativeFilesystem;
use ISAAC\CodeSnifferBaseliner\Filesystem\FileWriter;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\Tests\File\FileManipulatorFactory;
use ISAAC\CodeSnifferBaseliner\Tests\File\MemoryFilesystem;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\FileReportFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\MessageFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\Report\ReportFactory;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaselineCreatorTest extends TestCase
{
    /**
     * @var BaselineCreator
     */
    private $baselineCreator;
    /**
     * @var MemoryFilesystem
     */
    private $filesystem;
    /**
     * @var Runner&MockObject
     */
    private $runnerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new MemoryFilesystem();
        $this->runnerMock = $this->createMock(Runner::class);
        $this->baselineCreator = BaselinerCreatorFactory::create(
            $this->createMock(BasePathFinder::class),
            $this->runnerMock,
            null,
            $this->filesystem,
            null,
            $this->createMock(OutputWriter::class)
        );
    }

    public function testCreateBaseline(): void
    {
        $reportMessage = MessageFactory::createWithSourceAndLine('Violated.Rule', 2);
        $this->runnerMock->method('run')->willReturnOnConsecutiveCalls(
            ReportFactory::createWithFileReportsByFilename([
                'test.php' => FileReportFactory::createWithMessages([$reportMessage]),
            ]),
            ReportFactory::create()
        );

        $this->filesystem->replaceContents('test.php', <<<'PHP'
<?php
echo 'test';
PHP
);

        $this->baselineCreator->create();

        self::assertSame(
            <<<'PHP'
<?php
// phpcs:ignore Violated.Rule -- baseline
echo 'test';
PHP
,
            $this->filesystem->readContents('test.php')
        );
    }

    public function testCreateRepeatsAsLongAsFileAreChanged(): void
    {
        $reportMessage1 = MessageFactory::createWithSourceAndLine('Foo.Bar', 2);
        $reportMessage2 = MessageFactory::createWithSourceAndLine('Baz.Qux', 2);
        $this->runnerMock->method('run')->willReturnOnConsecutiveCalls(
            ReportFactory::createWithFileReportsByFilename([
                'test.php' => FileReportFactory::createWithMessages([$reportMessage1]),
            ]),
            ReportFactory::createWithFileReportsByFilename([
                'test.php' => FileReportFactory::createWithMessages([$reportMessage2]),
            ]),
            ReportFactory::create()
        );

        $this->filesystem->replaceContents('test.php', <<<'PHP'
<?php
echo 'test';
PHP
        );

        $this->baselineCreator->create();

        self::assertSame(
            <<<'PHP'
<?php
// phpcs:ignore Baz.Qux -- baseline
// phpcs:ignore Foo.Bar -- baseline
echo 'test';
PHP
            ,
            $this->filesystem->readContents('test.php')
        );
    }

}
