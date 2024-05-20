<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

interface ConsoleOutputInterface extends OutputInterface
{
    /**
     * Return the standard error output instance.
     */
    public function getErrorOutput(): OutputInterface;
}
