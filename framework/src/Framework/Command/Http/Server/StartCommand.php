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

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\DependencyInjection\ProjectMode;
use Neu\Component\Http\Exception\ExceptionInterface;
use Neu\Component\Http\Server\ServerInterface;
use Override;

#[Command(name: 'http:server:start', description: 'Starts the HTTP server in single-threaded mode, handling requests.')]
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

        $this->createSuccessBlock($output)->display('The server has started successfully.');

        $this->server->stop();

        $this->createSuccessBlock($output)->display('The server has stopped successfully.');

        return ExitCode::Success;
    }
}
