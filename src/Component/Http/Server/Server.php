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

use Amp\Http\Server\Driver\HttpDriver;
use Amp\Socket\ServerSocket;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Exception\ServerStateConflictException;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Neu\Component\Http\Server\Event\ServerStartedEvent;
use Psl\Async;
use Psl\Vec;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
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
     * @var list<ServerSocket>
     */
    private array $servers = [];

    /**
     * Stores active HTTP drivers keyed by client ID.
     *
     * @var array<int, HttpDriver>
     */
    private array $drivers = [];

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

        $this->logger->notice('Server is starting...');

        try {
            $servers = $this->infrastructure->createServerSockets();
            $clientFactory = $this->infrastructure->createClientFactory();
            foreach ($servers as $server) {
                $address = $server->getAddress();
                $context = $server->getBindContext();
                $scheme = $context->getTlsContext() !== null ? 'https' : 'http';
                $uri = $scheme . '://' . $address->toString();

                $this->logger->notice('Server listening on "{uri}".', [
                    'uri' => $uri,
                ]);

                $this->dispatcher->dispatch(new Event\ServerListeningEvent($address, $context));

                $this->servers[] = $server;
            }

            $httpDriverFactory = $servers->getReturn();
            foreach ($this->servers as $server) {
                EventLoop::queue(function () use ($server, $clientFactory, $httpDriverFactory): void {
                    while ($socket = $server->accept()) {
                        EventLoop::queue(function () use ($clientFactory, $httpDriverFactory, $socket): void {
                            try {
                                $client = $clientFactory->createClient($socket);
                                if (!$client) {
                                    $socket->close();
                                    return;
                                }

                                if ($this->status !== Status::Started) {
                                    $client->close();
                                    return;
                                }

                                $id = $client->getId();
                                $driver = $httpDriverFactory->createHttpDriver(
                                    new Internal\AmphpRequestHandler($this->runtime),
                                    new Internal\AmphpErrorHandler($this->logger),
                                    $client,
                                );

                                $this->drivers[$id] = $driver;

                                try {
                                    $driver->handleClient($client, $socket, $socket);
                                } finally {
                                    unset($this->drivers[$id]);
                                }
                            } catch (Throwable $exception) {
                                $this->logger->error('Exception while handling client ' . $socket->getRemoteAddress()->toString(), [
                                    'address' => $socket->getRemoteAddress(),
                                    'exception' => $exception,
                                ]);

                                $socket->close();
                            }
                        });
                    }
                });
            }

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

        try {
            foreach ($this->servers as $server) {
                $server->close();
            }

            Async\concurrently(Vec\map(
                $this->drivers,
                static fn (HttpDriver $driver) => $driver->stop(...),
            ));

            $this->logger->notice('Server stopped successfully.');
        } catch (Throwable $exception) {
            $this->logger->error('Error while stopping server.', [
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $this->status = Status::Stopped;
            $this->servers = [];
        }

        $this->dispatcher->dispatch(new Event\ServerStoppedEvent());
    }
}
