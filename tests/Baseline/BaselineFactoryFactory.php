<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests\Baseline;

use ISAAC\CodeSnifferBaseliner\Baseline\BaselineFactory;

class BaselineFactoryFactory
{
    public static function create(): BaselineFactory
    {
        return new BaselineFactory();
    }
}
