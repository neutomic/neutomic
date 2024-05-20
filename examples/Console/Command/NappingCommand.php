<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Psl\Async;

#[Command('napping', 'A command that takes a quick nap.')]
final readonly class NappingCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $output->writeLine('I got tired, I will take a quick nap for 5 seconds.');

        Async\sleep(5);

        $output->writeLine('I woke up!');

        return 0;
    }
}
