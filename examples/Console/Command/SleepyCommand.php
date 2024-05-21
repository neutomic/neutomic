<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Psl\Async;

#[Command('sleepy', 'A command that sleeps for 2 second.')]
final readonly class SleepyCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $output->writeLine('I got sleepy, I will sleep for 2 seconds.');

        Async\sleep(2);

        $output->writeLine('I woke up!');

        return 0;
    }
}