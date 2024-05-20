<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('i-am-disabled', 'Disabled Command', enabled: false)]
final readonly class DisabledCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $output->writeLine('<fg=red>This command is disabled, You should not see this message!</>');

        return 0;
    }
}
