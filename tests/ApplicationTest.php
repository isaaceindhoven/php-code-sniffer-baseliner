<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Tests;

use ISAAC\CodeSnifferBaseliner\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testCreate(): void
    {
        Application::create();
        $this->addToAssertionCount(1);
    }
}
