<?php

declare(strict_types=1);

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Neu\Component\Console\Table\TableFactoryTrait;

/**
 * Example: `php examples/Console/entrypoint.php options --host=127.0.0.1 -p=1337 -u azjezz -s secret`.
 */
#[Command('options', 'Example of using options in command', options: new Input\Bag\OptionBag([
    new Input\Definition\Option('host', alias: 'u', description: 'The host to connect to', mode: Input\Definition\Mode::Required),
    new Input\Definition\Option('port', description: 'The port to connect to ( defaults to 1337 )', mode: Input\Definition\Mode::Optional),
    new Input\Definition\Option('user', description: 'The user to connect as', mode: Input\Definition\Mode::Optional),
    new Input\Definition\Option('secret', description: 'The secret to connect with', mode: Input\Definition\Mode::Optional),
]))]
final readonly class OptionsCommand implements CommandInterface
{
    use TableFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');
        $user = $input->getOption('user');
        $secret = $input->getOption('secret');

        $headers = ['Option', 'Alias', 'Exists', 'Value'];
        $rows = [
            [$host->getName(), $host->getAlias(), $host->exists() ? 'Yes' : 'No', $host->getValue()],
            [$port->getName(), $port->getAlias(), $port->exists() ? 'Yes' : 'No', $port->exists() ? $port->getValue() : 'N/A'],
            [$user->getName(), $user->getAlias(), $user->exists() ? 'Yes' : 'No', $user->exists() ? $user->getValue() : 'N/A'],
            [$secret->getName(), $secret->getAlias(), $secret->exists() ? 'Yes' : 'No', $secret->exists() ? $secret->getValue() : 'N/A'],
        ];

        $this->createAsciiTable($output, $headers, $rows)->display();

        return 0;
    }
}
