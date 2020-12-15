<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\BaselineCleaner;
use ISAAC\CodeSnifferBaseliner\Tests\File\MemoryFilesystem;
use PHPUnit\Framework\TestCase;

class BaselineCleanerTest extends TestCase
{
    /**
     * @var BaselineCleaner
     */
    private $baselineCleaner;
    /**
     * @var MemoryFilesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new MemoryFilesystem();
        $this->baselineCleaner = BaselineCleanerFactory::createWithFilesystem($this->filesystem);
    }

    public function testCleanup(): void
    {
        $initialContents = <<<'PHP'
<?php
// phpcs:ignore Foo.Bar -- baseline
echo 'test';
PHP;
        $expectedContents = <<<'PHP'
<?php
echo 'test';
PHP;

        $this->filesystem->replaceContents('test.php', $initialContents);

        $this->baselineCleaner->cleanUp();

        self::assertSame($expectedContents, $this->filesystem->readContents('test.php'));
    }
}
