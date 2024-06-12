<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server;

use Amp\Pipeline\Queue;
use Amp\Socket;
use Neu\Component\Broadcast\Address\TcpAddress;
use Neu\Component\Broadcast\Address\UnixAddress;
use Neu\Component\Broadcast\Server\Exception\ServerStateConflictException;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use function Amp\async;
use function Psl\invariant;

final class Server implements ServerInterface
{
    private Status $status = Status::Stopped;

    private Socket\ResourceServerSocket|null $server;

    private UnixAddress|TcpAddress|null $address;

    /**
     * @var array<non-empty-string, Socket\ResourceSocket>
     */
    private array $clients = [];
    private Queue $queue;

    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    )
    {
        $this->queue = new Queue();
    }

    public function start(UnixAddress|TcpAddress $address): void
    {
        if ($this->status === Status::Started) {
            return;
        }

        if ($this->status !== Status::Stopped) {
            throw new ServerStateConflictException('The server is in a conflicting state.');
        }

        $this->status = Status::Starting;

        $this->logger->notice('Server is starting...');

        $this->logger->notice(sprintf('Starting broadcast server at socket %s', $address->toString()));

        try {
            $this->address = $address;
            $this->server = $this->createServerSocket();

            async(function () {
                while ($client = $this->server->accept()) {
                    async(function () use ($client) {
                        $this->logger->notice('New client connected', ['localAddress' => $client->getLocalAddress()]);

                        $clientId = uniqid('', true);
                        $this->clients[$clientId] = $client;

                        async(function () use ($client) {
                            foreach ($client->getIterator() as $message) {
                                $this->queue->pushAsync($message);
                            }
                        });

                        $client->onClose(function () use ($client, $clientId) {
                            unset($this->clients[$clientId]);
                            $this->logger->notice('Client disconnected', ['localAddress' => $client->getLocalAddress()]);
                        });
                    });
                }
            });

            async(function () {
                foreach ($this->queue->iterate() as $message) {
                    $this->broadcast($message);
                }
            });

            $this->status = Status::Started;
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
    }

    public function stop(): void
    {
        if ($this->status === Status::Stopped) {
            return;
        }

        if ($this->status !== Status::Started) {
            throw new ServerStateConflictException('The server is in a conflicting state. '.$this->status->value);
        }

        $this->status = Status::Stopping;

        $this->logger->notice('Server is stopping...');

        try {
            invariant(null !== $this->address, 'There must be an address');
            invariant(null !== $this->server, 'There must be a server');

            $this->queue->complete();

            foreach ($this->clients as $client) {
                $this->logger->info('Closing connection '. $client->getLocalAddress());
                $client->end();
            }

            $this->server->close();

            $this->logger->notice('Server stopped successfully.');
        } catch (Throwable $exception) {
             $this->logger->error('Error while stopping server.', [
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $this->status = Status::Stopped;
            $this->clients = [];
            $this->server = null;
            if ($this->address instanceof UnixAddress) {
                unlink($this->address->socket);
            }
        }
    }

    /**
     * @param string $message
     * @param non-empty-string $from
     */
    private function broadcast(string $message): void
    {
        foreach ($this->clients as $clientId => $client) {
            $client->write($message);
        }
    }

    private function createServerSocket(): Socket\ResourceServerSocket
    {
        if ($this->address instanceof TcpAddress) {
            /** @psalm-suppress MissingThrowsDocblock */
            throw new \RuntimeException('Not implemented');
        }

        return Socket\listen('unix://' . $this->address->socket);
    }
}
