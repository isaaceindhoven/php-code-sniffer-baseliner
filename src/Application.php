<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use Exception;
use InvalidArgumentException;
use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;
use ISAAC\CodeSnifferBaseliner\Command\CreateBaseline;
use ISAAC\CodeSnifferBaseliner\Command\ShowHelp;
use ISAAC\CodeSnifferBaseliner\Filesystem\NativeFilesystem;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;
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
        return new self(
            new BaselineCreator(
                new BasePathFinder(),
                new Runner(),
                new BaselineFactory(),
                new NativeFilesystem(),
                new AddBaselineProcessor(new InstructionCommentLineParser()),
                new OutputWriter()
            )
        );
    }

    /**
     * @var BaselineCreator
     */
    private $baselineCreator;

    public function __construct(
        BaselineCreator $baselineCreator
    ) {
        $this->baselineCreator = $baselineCreator;
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

HELP;
    }
}
