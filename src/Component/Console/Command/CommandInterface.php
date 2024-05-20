<?php

declare(strict_types=1);

namespace Neu\Component\Console\Command;

use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

interface CommandInterface
{
    /**
     * The method that stores the code to be executed when the command is run.
     */
    public function run(InputInterface $input, OutputInterface $output): ExitCode|int;
}
