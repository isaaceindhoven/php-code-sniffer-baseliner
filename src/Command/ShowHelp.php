<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Command;

class ShowHelp
{
    /**
     * @var string
     */
    private $commandName;

    public function __construct(string $commandName)
    {
        $this->commandName = $commandName;
    }

    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
