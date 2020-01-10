<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner;

use const PHP_EOL;

class BaselineRemover
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

    public function remove(): void
    {
        echo 'Removing the baseline from the config file ...' . PHP_EOL;

        $basePath = $this->basePathFinder->findBasePath();
        $configFilename = $this->configFileFinder->findConfigFile($basePath);
        $config = $this->configFileReader->readConfig($configFilename);
        $config->removeBaseline();
        $this->configFileWriter->writeConfig($config, $configFilename);

        echo 'Done removing the baseline!' . PHP_EOL;
    }
}
