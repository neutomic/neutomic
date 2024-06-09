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

namespace Neu\Component\Http\Server;

use Amp\Http\Server\SocketHttpServer;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Neu\Component\Http\Server\Event\ServerStartedEvent;
use Neu\Component\Http\Server\Event\ServerStoppingEvent;
use Neu\Component\Http\Server\Exception\ServerStateConflictException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * A server that listens for incoming HTTP requests and dispatches them to the appropriate handler.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class Server implements ServerInterface
{
    /**
     * The infrastructure used to create servers.
     */
    private ServerInfrastructure $infrastructure;

    /**
     * The runtime used to handle requests.
     */
    private RuntimeInterface $runtime;

    /**
     * The event dispatcher used to dispatch events.
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The logger used to log events.
     */
    private LoggerInterface $logger;

    /**
     * The current status of the server.
     */
    private Status $status = Status::Stopped;

    /**
     * The socket HTTP server instance.
     */
    private null|SocketHttpServer $socketHttpServer = null;

    public function __construct(ServerInfrastructure $infrastructureFactory, RuntimeInterface $runtime, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = new NullLogger())
    {
        $this->infrastructure = $infrastructureFactory;
        $this->runtime = $runtime;
        $this->dispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        if ($this->status === Status::Started) {
            return;
        }

        if ($this->status !== Status::Stopped) {
            throw new ServerStateConflictException('The server is in a conflicting state.');
        }

        $this->status = Status::Starting;
        $this->socketHttpServer = $socketHttpServer = $this->infrastructure->createSocketHttpServer();

        $this->logger->notice('Server is starting...');

        try {
            $socketHttpServer->start(
                new Internal\AmphpRequestHandler($this->runtime),
                new Internal\AmphpErrorHandler($this->logger),
            );

            $this->status = Status::Started;

            $this->logger->notice('Server started successfully.');
        } catch (Throwable $exception) {
            $this->logger->error('Error while starting server.', [
                'exception' => $exception,
            ]);

            try {
                $this->status = Status::Started;
                $this->stop();
            } finally {
                throw $exception;
            }
        }

        foreach ($this->infrastructure->getUrls() as $url) {
            $this->logger->notice('Server is listening on "{url}".', ['url' => $url]);
        }

        $this->dispatcher->dispatch(new ServerStartedEvent());
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        if ($this->status === Status::Stopped) {
            return;
        }

        if ($this->status !== Status::Started) {
            throw new ServerStateConflictException('The server is in a conflicting state.');
        }

        $this->status = Status::Stopping;

        $this->logger->notice('Server is stopping...');

        $socketHttpServer = $this->socketHttpServer;

        assert($socketHttpServer !== null);

        // Dispatch the stopping event before closing the servers, so that listeners can perform cleanup (e.g. close broadcasting hubs).
        $this->dispatcher->dispatch(new ServerStoppingEvent());

        try {
            $socketHttpServer->stop();

            $this->logger->notice('Server stopped successfully.');
        } catch (Throwable $exception) {
            $this->logger->error('Error while stopping server.', [
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $this->status = Status::Stopped;
            $this->socketHttpServer = null;
        }

        $this->dispatcher->dispatch(new Event\ServerStoppedEvent());
    }
}
