<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('hidden', 'I am hidden', hidden: true)]
final readonly class HiddenCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $output->writeLine('<fg=yellow;bold>You discovered me!</>');

        return 0;
    }
}
