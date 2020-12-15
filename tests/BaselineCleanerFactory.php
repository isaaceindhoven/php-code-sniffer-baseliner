<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\BaselineCleaner;
use ISAAC\CodeSnifferBaseliner\BasePathFinder;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileFinder;
use ISAAC\CodeSnifferBaseliner\Config\ConfigFileReader;
use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\RemoveBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Tests\Config\ConfigFileFinderFactory;
use ISAAC\CodeSnifferBaseliner\Tests\Config\ConfigFileReaderFactory;
use ISAAC\CodeSnifferBaseliner\Tests\File\FilesystemFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\RunnerFactory;
use ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor\RemoveBaselineProcessorFactory;
use ISAAC\CodeSnifferBaseliner\Tests\Util\OutputWriterFactory;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;

class BaselineCleanerFactory
{
    public static function create(
        ?BasePathFinder $basePathFinder = null,
        ?ConfigFileFinder $configFileFinder = null,
        ?ConfigFileReader $configFileReader = null,
        ?Filesystem $filesystem = null,
        ?Runner $runner = null,
        ?RemoveBaselineProcessor $removeBaselineProcessor = null,
        ?OutputWriter $outputWriter = null
    ): BaselineCleaner {
        return new BaselineCleaner(
            $basePathFinder !== null ? $basePathFinder : BasePathFinderFactory::create(),
            $configFileFinder !== null ? $configFileFinder : ConfigFileFinderFactory::create(),
            $configFileReader !== null ? $configFileReader : ConfigFileReaderFactory::create(),
            $filesystem !== null ? $filesystem : FilesystemFactory::create(),
            $runner !== null ? $runner : RunnerFactory::create(),
            $removeBaselineProcessor !== null ? $removeBaselineProcessor : RemoveBaselineProcessorFactory::create(),
            $outputWriter !== null ? $outputWriter : OutputWriterFactory::create()
        );
    }

    public static function createWithFilesystem(Filesystem $filesystem): BaselineCleaner
    {
        return self::create(null, null, null, $filesystem);
    }
}
