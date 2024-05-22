<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;

#[Command('arguments', 'Example of using arguments in command', arguments: new Input\Bag\ArgumentBag([
    new Input\Definition\Argument('name', description: 'The name of the user', mode: Input\Definition\Mode::Required),
    new Input\Definition\Argument('age', description: 'The age of the user', mode: Input\Definition\Mode::Optional),
]))]
final readonly class ArgumentsCommand implements CommandInterface
{
    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $age = $input->getArgument('age');
        $output->writeLine("<underline>Hello, <fg=yellow>{$name->getValue()}</>!</>");
        if ($age->exists()) {
            $output->writeLine("<underline>You are <fg=yellow>{$age->getValue()}</> years old.</>");
        } else {
            $output->writeLine("<underline>You did not provide your age.</>");
        }

        return 0;
    }
}
