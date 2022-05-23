<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;
use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;

use function sprintf;

class BaselineCreator
{
    /**
     * @var BasePathFinder
     */
    private $basePathFinder;
    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var BaselineFactory
     */
    private $baselineFactory;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var AddBaselineProcessor
     */
    private $addBaselineProcessor;
    /**
     * @var OutputWriter
     */
    private $outputWriter;

    public function __construct(
        BasePathFinder $basePathFinder,
        Runner $runner,
        BaselineFactory $baselineFactory,
        Filesystem $filesystem,
        AddBaselineProcessor $addBaselineProcessor,
        OutputWriter $outputWriter
    ) {
        $this->basePathFinder = $basePathFinder;
        $this->runner = $runner;
        $this->baselineFactory = $baselineFactory;
        $this->filesystem = $filesystem;
        $this->addBaselineProcessor = $addBaselineProcessor;
        $this->outputWriter = $outputWriter;
    }

    public function create(): void
    {
        $basePath = $this->basePathFinder->findBasePath();

        do {
            $this->outputWriter->writeLine('Running PHP_CodeSniffer (this may take a while)...');

            $report = $this->runner->run($basePath);

            if ($report->getTotals()->getErrors() === 0 && $report->getTotals()->getWarnings() === 0) {
                $this->outputWriter->writeLine('PHP_CodeSniffer did not report any errors.');
                return;
            }

            $this->outputWriter->writeLine('Creating the baseline...');

            $baseline = $this->baselineFactory->createFromReport($report);

            $this->outputWriter->writeLine('Adding baseline comments to PHP files...');

            $baselineHasBeenChanged = $this->writeBaseline($baseline);
        } while ($baselineHasBeenChanged);

        $this->outputWriter->writeLine('Done creating the baseline!');
    }

    private function writeBaseline(Baseline\Baseline $baseline): bool
    {
        $baselineHasBeenChanged = false;
        foreach ($baseline->getViolatedRulesByFileAndLineNumber() as $file => $violatedRulesByLineNumber) {
            $fileHasBeenChanged = $this->addRuleExclusionsByLineNumber(
                $file,
                $violatedRulesByLineNumber
            );
            $baselineHasBeenChanged = $baselineHasBeenChanged || $fileHasBeenChanged;
        }
        return $baselineHasBeenChanged;
    }

    /**
     * @param array<array<string>> $ruleExclusionsByLineNumber
     */
    public function addRuleExclusionsByLineNumber(string $file, array $ruleExclusionsByLineNumber): bool
    {
        $fileContents = $this->filesystem->readContents($file);
        $modifiedFileContents = $this->addBaselineProcessor->addRuleExclusionsByLineNumber(
            $fileContents,
            $ruleExclusionsByLineNumber
        );
        if ($fileContents !== $modifiedFileContents) {
            $this->filesystem->replaceContents($file, $modifiedFileContents);
            $this->outputWriter->writeLine(sprintf('%s has been modified.', $file));
            return true;
        }
        return false;
    }
}
