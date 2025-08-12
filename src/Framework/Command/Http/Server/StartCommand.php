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

namespace Neu\Framework\Command\Http\Server;

use Amp\Cluster\Cluster;
use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\DependencyInjection\ProjectMode;
use Neu\Component\Http\Exception\ExceptionInterface;
use Neu\Component\Http\Server\ServerInterface;
use Revolt\EventLoop\UnsupportedFeatureException;
use Override;

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

    /**
     * @inheritDoc
     *
     * @throws ExceptionInterface If an error occurs while starting or stopping the server.
     */
    #[Override]
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
        } catch (UnsupportedFeatureException) {
            $this->createWarningBlock($output)->display(
                'Signal handling is not supported on this platform. The server will not be able to gracefully shut down.'
            );
        }

        $this->server->stop();

        $this->createSuccessBlock($output)->display(
            'The server has stopped successfully.'
        );

        return ExitCode::Success;
    }
}
