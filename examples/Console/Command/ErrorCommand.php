<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Exception;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('error', 'I am not well')]
final readonly class ErrorCommand implements CommandInterface
{
    /**
     * @throws Exception
     */
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        throw new Exception('I am not well');
    }
}
