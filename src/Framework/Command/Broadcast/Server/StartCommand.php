<?php

declare(strict_types=1);

namespace Neu\Framework\Command\Broadcast\Server;

use Neu;
use Neu\Component\Broadcast\Address\TcpAddress;
use Neu\Component\Broadcast\Address\UnixAddress;
use Neu\Component\Broadcast\Server\ServerInterface;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Option;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Revolt\EventLoop\UnsupportedFeatureException;
use function Amp\trapSignal;

#[Command(
    name: 'broadcast:server:start',
    description: 'Starts the socket broadcast server',
    options: new OptionBag([
        new Option('socket', 's', 'Unix socket path')
        // new Option('host', 'h', 'Host address to listen'),
        // new Option('port', 'p', 'Port to listen'),
    ])
)]
final class StartCommand implements CommandInterface
{
    use BlockFactoryTrait;

    public function __construct(private ServerInterface $server)
    {
    }

    /**
     * @throws Neu\Component\Console\Exception\MissingValueException
     */
    public function run(InputInterface $input, OutputInterface $output): ExitCode|int
    {
        if (null === $address = $this->getAddress($input)) {
            $this->createErrorBlock($output)->display('--socket option is required');
            return ExitCode::Failure;
        }

        $this->server->start($address);

        try {
            $output->writeLine('Awaiting termination');
            trapSignal([
                SIGHUP,
                SIGINT,
                SIGQUIT,
                SIGTERM,
            ]);
            // Cluster::awaitTermination();
            $output->writeLine('Terminated');
        } catch (UnsupportedFeatureException) {
            $this->createWarningBlock($output)->display(
                'Signal handling is not supported on this platform. The server will not be able to gracefully shut down.'
            );
        }


        $output->writeLine('Stopping');
        $this->server->stop();
        $output->writeLine('Stopped');


        return ExitCode::Success;
    }

    private function getAddress(InputInterface $input): UnixAddress|TcpAddress|null
    {
        $socketOption = $input->getOption('socket');

        if ($socketOption->exists() && '' !== $socket = $socketOption->getValue()) {
            return new UnixAddress($socket);
        }

        return null;
    }
}
