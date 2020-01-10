<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;

use const PHP_EOL;

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
     * @var Config\ConfigFileWriter
     */
    private $configFileWriter;

    public function __construct(
        BasePathFinder $basePathFinder,
        Config\ConfigFileFinder $configFileFinder,
        Config\ConfigFileReader $configFileReader,
        Config\ConfigFileWriter $configFileWriter
    ) {
        $this->basePathFinder = $basePathFinder;
        $this->configFileFinder = $configFileFinder;
        $this->configFileReader = $configFileReader;
        $this->configFileWriter = $configFileWriter;
    }

    public function cleanUp(): void
    {
        $basePath = $this->basePathFinder->findBasePath();
        $configFilename = $this->configFileFinder->findConfigFile($basePath);

        echo 'Temporary removing the baseline from the config file...' . PHP_EOL;

        $config = $this->configFileReader->readConfig($configFilename);
        $temporaryConfig = clone $config;
        $temporaryConfig->removeBaseline();
        $this->configFileWriter->writeConfig($temporaryConfig, $configFilename);

        echo 'Running PHP_CodeSniffer (this may take a while)...' . PHP_EOL;

        $report = (new Runner())->run($basePath);

        echo 'Removing the baseline rules that pass...' . PHP_EOL;

        $config->removeBaselineExclusionsNotInBaseline($report->createBaseline());
        $this->configFileWriter->writeConfig($config, $configFilename);

        echo 'Done cleaning up the baseline!' . PHP_EOL;
    }
}
