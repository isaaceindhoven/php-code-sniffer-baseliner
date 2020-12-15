<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use Exception;
use InvalidArgumentException;
use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;
use ISAAC\CodeSnifferBaseliner\Command\CleanUpBaseline;
use ISAAC\CodeSnifferBaseliner\Command\CreateBaseline;
use ISAAC\CodeSnifferBaseliner\Command\RemoveBaseline;
use ISAAC\CodeSnifferBaseliner\Command\ShowHelp;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileFinder;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileReader;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileWriter;
use ISAAC\CodeSnifferBaseliner\Filesystem\NativeFilesystem;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Filesystem\FileProcessor;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\RemoveBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;
use Throwable;

use function array_shift;
use function count;
use function get_class;
use function sprintf;

use const PHP_EOL;

class Application
{
    public static function create(): self
    {
        $basePathFinder = new BasePathFinder();
        $configFileFinder = new ConfigFileFinder();
        $configFileReader = new ConfigFileReader();
        $configFileWriter = new ConfigFileWriter();
        $filesystem = new NativeFilesystem();
        $runner = new Runner();
        $outputWriter = new OutputWriter();
        return new self(
            new BaselineCreator(
                $basePathFinder,
                $runner,
                new BaselineFactory(),
                $filesystem,
                new AddBaselineProcessor(),
                $outputWriter
            ),
            new BaselineCleaner(
                $basePathFinder,
                $configFileFinder,
                $configFileReader,
                $filesystem,
                $runner,
                new RemoveBaselineProcessor(),
                $outputWriter
            ),
            new BaselineRemover($basePathFinder, $configFileFinder, $configFileReader, $configFileWriter)
        );
    }

    /**
     * @var BaselineCreator
     */
    private $baselineCreator;
    /**
     * @var BaselineCleaner
     */
    private $baselineCleaner;
    /**
     * @var BaselineRemover
     */
    private $baselineRemover;

    public function __construct(
        BaselineCreator $baselineCreator,
        BaselineCleaner $baselineCleaner,
        BaselineRemover $baselineRemover
    ) {
        $this->baselineCreator = $baselineCreator;
        $this->baselineCleaner = $baselineCleaner;
        $this->baselineRemover = $baselineRemover;
    }

    public function run(string ...$arguments): int
    {
        try {
            $command = $this->getCommandForArguments(...$arguments);
            $this->runCommand($command);
        } catch (Throwable $throwable) {
            echo ((string) $throwable) . PHP_EOL;
            return 1;
        }
        return 0;
    }

    /**
     * @throws Exception
     */
    private function getCommandForArguments(string ...$arguments): object
    {
        if (count($arguments) === 0) {
            throw new InvalidArgumentException('Please provide at least one argument.');
        }
        $binaryName = array_shift($arguments);
        if (count($arguments) === 0) {
            return $this->getShowHelpCommand();
        }
        $commandName = array_shift($arguments);
        switch ($commandName) {
            case 'create-baseline':
                return new CreateBaseline();
            case 'clean-up-baseline':
                return new CleanUpBaseline();
            case 'remove-baseline':
                return new RemoveBaseline();
            case 'help':
            default:
                return $this->getShowHelpCommand($binaryName);
        }
    }

    private function getShowHelpCommand(?string $commandName = null): ShowHelp
    {
        return new ShowHelp($commandName !== null ? $commandName : 'phpcs-baseliner');
    }

    private function runCommand(object $command): void
    {
        if ($command instanceof CreateBaseline) {
            $this->baselineCreator->create();
        } elseif ($command instanceof CleanUpBaseline) {
            $this->baselineCleaner->cleanUp();
        } elseif ($command instanceof RemoveBaseline) {
            $this->baselineRemover->remove();
        } elseif ($command instanceof ShowHelp) {
            $this->showHelp($command);
        } else {
            throw new InvalidArgumentException(
                sprintf('Unable to handle command of type \'%s\'.', get_class($command))
            );
        }
    }

    private function showHelp(ShowHelp $command): void
    {
        echo <<<HELP
Usage: {$command->getCommandName()} [command]

Available commands:
  create-baseline      Create a new baseline
  clean-up-baseline    Remove errors from the baseline that do not occur anymore
  remove-baseline      Remove the baseline

HELP;
    }
}
