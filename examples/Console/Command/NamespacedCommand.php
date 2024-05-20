<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\AbstractCommand;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

#[Command(name: 'database:schema:update', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:delete', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:validate', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:generate', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:diff', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:dump', description: 'This is a namespaced command')]
#[Command(name: 'database:schema:load', description: 'This is a namespaced command')]
#[Command(name: 'database:query', description: 'This is a namespaced command')]
#[Command(name: 'database:migration:generate', description: 'This is a namespaced command')]
#[Command(name: 'database:migration:execute', description: 'This is a namespaced command')]
#[Command(name: 'database:migration:rollback', description: 'This is a namespaced command', enabled: false)]
final readonly class NamespacedCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createSectionBlock($this->output)
            ->display('This is a namespaced command')
            ->display('Using a namespace as part of your command name, helps you to group related commands together and avoid conflicts with other commands.')
            ->display('This is a good practice when you have a lot of commands in your application.')
            ->display('The application help screen will display the namespace as a group of commands.')
        ;

        $this->createWarningBlock($this->output)
            ->display('This command uses multiple configurations to demonstrate how to group related commands together.')
            ->display('Meaning it has multiple names and descriptions, but they all point to the same command object.')
        ;

        return 0;
    }
}
