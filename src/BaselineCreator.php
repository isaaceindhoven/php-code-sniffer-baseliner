<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use ISAAC\CodeSnifferBaseliner\Config\ConfigFileFinder;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileReader;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileWriter;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;

use const PHP_EOL;

class BaselineCreator
{
    /**
     * @var BasePathFinder
     */
    private $basePathFinder;
    /**
     * @var ConfigFileFinder
     */
    private $configFileFinder;
    /**
     * @var ConfigFileReader
     */
    private $configFileReader;
    /**
     * @var ConfigFileWriter
     */
    private $configFileWriter;

    public function __construct(
        BasePathFinder $basePathFinder,
        ConfigFileFinder $configFileFinder,
        ConfigFileReader $configFileReader,
        ConfigFileWriter $configFileWriter
    ) {
        $this->basePathFinder = $basePathFinder;
        $this->configFileFinder = $configFileFinder;
        $this->configFileReader = $configFileReader;
        $this->configFileWriter = $configFileWriter;
    }

    public function create(): void
    {
        $basePath = $this->basePathFinder->findBasePath();
        $configFilename = $this->configFileFinder->findConfigFile($basePath);

        echo 'Running PHP_CodeSniffer (this may take a while)...' . PHP_EOL;

        $report = (new Runner())->run($basePath);

        if ($report->getTotals()->getErrors() === 0 && $report->getTotals()->getWarnings() === 0) {
            echo 'PHP_CodeSniffer did not report any errors.' . PHP_EOL;
            return;
        }

        echo 'Creating the baseline...' . PHP_EOL;

        $baseline = $report->createBaseline();

        echo 'Merging the baseline with the config file...' . PHP_EOL;

        $this->configureBaseline($configFilename, $baseline);

        echo 'Done creating the baseline!' . PHP_EOL;
    }

    private function configureBaseline(string $configFilename, Baseline\Baseline $baseline): void
    {
        $config = $this->configFileReader->readConfig($configFilename);
        $config->mergeBaseline($baseline);
        $this->configFileWriter->writeConfig($config, $configFilename);
    }
}
