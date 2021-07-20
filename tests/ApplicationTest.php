<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\Application;
use PHPUnit\Framework\TestCase;

use function chdir;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function shell_exec;
use function sprintf;

class ApplicationTest extends TestCase
{
    private const TEST_FILE_PATH = __DIR__ . '/Fixtures/src/test.php';
    private const TEST_SOURCE_FILE_CONTENTS = <<<'PHP'
<?php declare(strict_types=1);

namespace Foo;

class Bar {
}

PHP;
    // phpcs:disable SlevomatCodingStandard.Files.LineLength.LineTooLong
    private const EXPECTED_TEST_SOURCE_FILE_CONTENTS = <<<'PHP'
<?php declare(strict_types=1); // phpcs:ignore PSR12.Files.FileHeader.SpacingAfterBlock, PSR12.Files.OpenTag.NotAlone, SlevomatCodingStandard.Files.LineLength.LineTooLong, SlevomatCodingStandard.TypeHints.DeclareStrictTypes.IncorrectWhitespaceAfterDeclare, SlevomatCodingStandard.TypeHints.DeclareStrictTypes.IncorrectWhitespaceBeforeDeclare -- baseline

namespace Foo;

// phpcs:ignore PSR2.Classes.ClassDeclaration.OpenBraceNewLine -- baseline
class Bar {
}

PHP;
    // phpcs:enable SlevomatCodingStandard.Files.LineLength.LineTooLong

    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = Application::create();
    }

    public function testRun(): void
    {
        if (!file_exists(dirname(self::TEST_FILE_PATH))) {
            mkdir(dirname(self::TEST_FILE_PATH), 0777, true);
        }
        file_put_contents(self::TEST_FILE_PATH, self::TEST_SOURCE_FILE_CONTENTS);

        $originalCwd = getcwd();
        self::assertNotFalse($originalCwd);
        chdir(sprintf('%s/Fixtures', __DIR__));
        try {
            shell_exec('composer install 2>&1');
            ob_start();
            $this->application->run('phpcs-baseliner', 'create-baseline');
            $output = ob_get_clean();
            self::assertNotFalse($output);
        } finally {
            chdir($originalCwd);
        }

        self::assertSame(
            self::EXPECTED_TEST_SOURCE_FILE_CONTENTS,
            file_get_contents(self::TEST_FILE_PATH),
            $output
        );
    }
}
