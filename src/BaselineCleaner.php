<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\RemoveBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;

class BaselineCleaner
{
    /**
     * @var BasePathFinder
     */
    private $basePathFinder;
    /**
     * @var Config\ConfigFileFinder
     */
    private $configFileFinder;
    /**
     * @var Config\ConfigFileReader
     */
    private $configFileReader;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var RemoveBaselineProcessor
     */
    private $removeBaselineProcessor;
    /**
     * @var OutputWriter
     */
    private $outputWriter;

    public function __construct(
        BasePathFinder $basePathFinder,
        Config\ConfigFileFinder $configFileFinder,
        Config\ConfigFileReader $configFileReader,
        Filesystem $filesystem,
        Runner $runner,
        RemoveBaselineProcessor $removeBaselineProcessor,
        OutputWriter $outputWriter
    ) {
        $this->basePathFinder = $basePathFinder;
        $this->configFileFinder = $configFileFinder;
        $this->configFileReader = $configFileReader;
        $this->filesystem = $filesystem;
        $this->runner = $runner;
        $this->removeBaselineProcessor = $removeBaselineProcessor;
        $this->outputWriter = $outputWriter;
    }

    public function cleanUp(): void
    {
        $basePath = $this->basePathFinder->findBasePath();
        $configFilename = $this->configFileFinder->findConfigFile($basePath);

        $config = $this->configFileReader->readConfig($configFilename);
        foreach ($this->generateFilenamesRecursively($basePath, $config->getFiles()) as $filePath) {
            $this->cleanUpFile($filePath);
        }
    }

    private function generateFilenamesRecursively(string $directory, iterable $filenames): iterable
    {
        foreach ($filenames as $filename) {
            $filePath = $directory . '/' . $filename;
            if ($this->filesystem->isFile($filePath)) {
                yield $filename;
            } elseif ($this->filesystem->isDirectory($filePath)) {
                $directoryPath = $filePath;
                $filesInDirectory = $this->generateFilenamesRecursively(
                    $directoryPath,
                    $this->filesystem->getFilesInDirectory($filePath)
                );
                foreach ($filesInDirectory as $filenameInDirectory) {
                    yield $directoryPath . '/' . $filenameInDirectory;
                }
            }
        }
    }

    private function cleanUpFile(string $filePath): void
    {
        $originalFileContents = $this->filesystem->readContents($filePath);
        $modifiedFileContents = $this->removeBaselineProcessor->removeBaselineFromFile($originalFileContents);
        $this->filesystem->replaceContents($filePath, $modifiedFileContents);
    }
}
