<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

final readonly class HelloWorldCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $output->writeLine('Hello World!');

        return 0;
    }
}
