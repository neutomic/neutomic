<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Command;

use Amp\Cluster\Cluster;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\DependencyInjection\ProjectMode;
use Neu\Component\Http\Server\ServerInterface;
use Revolt\EventLoop\UnsupportedFeatureException;

#[Command(
    name: 'http:server:start',
    description: 'Starts the HTTP server in single-threaded mode, handling requests without clustering.',
)]
final readonly class StartCommand implements CommandInterface
{
    use BlockFactoryTrait;

    private ProjectMode $mode;
    private ServerInterface $server;

    public function __construct(ProjectMode $mode, ServerInterface $server)
    {
        $this->mode = $mode;
        $this->server = $server;
    }

    public function run(InputInterface $input, OutputInterface $output): ExitCode
    {
        $this->server->start();

        $this->createSuccessBlock($output)->display(
            'The server has started successfully.'
        );

        if ($this->mode->isProduction()) {
            $this->createWarningBlock($output)->display(
                'The server is running in single-threaded mode, which is not suitable for production environments.' .
                ' Use the "http:server:cluster" command to start the server in clustered mode.'
            );
        }

        try {
            Cluster::awaitTermination();

            $this->server->stop();

            $this->createSuccessBlock($output)->display(
                'The server has stopped successfully.'
            );
        } catch (UnsupportedFeatureException) {
            $this->createWarningBlock($output)->display(
                'Signal handling is not supported on this platform. The server will not be able to gracefully shut down.'
            );
        }

        return ExitCode::Success;
    }
}
