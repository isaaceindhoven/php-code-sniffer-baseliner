<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\BasePathFinder;

class BasePathFinderFactory
{
    public static function create(): BasePathFinder
    {
        return new BasePathFinder();
    }
}
