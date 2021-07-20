<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;
use ISAAC\CodeSnifferBaseliner\BaselineCreator;
use ISAAC\CodeSnifferBaseliner\BasePathFinder;
use ISAAC\CodeSnifferBaseliner\Filesystem\Filesystem;
use ISAAC\CodeSnifferBaseliner\PhpCodeSnifferRunner\Runner;
use ISAAC\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use ISAAC\CodeSnifferBaseliner\Tests\Filesystem\FilesystemFactory;
use ISAAC\CodeSnifferBaseliner\Tests\PhpCodeSnifferRunner\RunnerFactory;
use ISAAC\CodeSnifferBaseliner\Tests\SourceCodeProcessor\AddBaselineProcessorFactory;
use ISAAC\CodeSnifferBaseliner\Util\OutputWriter;

class BaselinerCreatorFactory
{
    public static function create(
        ?BasePathFinder $basePathFinder = null,
        ?Runner $runner = null,
        ?BaselineFactory $baselineFactory = null,
        ?Filesystem $filesystem = null,
        ?AddBaselineProcessor $addBaselineProcessor = null,
        ?OutputWriter $outputWriter = null
    ): BaselineCreator {
        return new BaselineCreator(
            $basePathFinder !== null ? $basePathFinder : BasePathFinderFactory::create(),
            $runner !== null ? $runner : RunnerFactory::create(),
            $baselineFactory !== null ? $baselineFactory : Baseline\BaselineFactoryFactory::create(),
            $filesystem !== null ? $filesystem : FilesystemFactory::create(),
            $addBaselineProcessor !== null ? $addBaselineProcessor : AddBaselineProcessorFactory::create(),
            $outputWriter !== null ? $outputWriter : Util\OutputWriterFactory::create()
        );
    }
}
