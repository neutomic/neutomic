<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server;

use Amp\Pipeline\Queue;
use Amp\Socket;
use Neu\Component\Broadcast\Dsn;
use Neu\Component\Broadcast\Server\Exception\InvalidArgumentException;
use Neu\Component\Broadcast\Server\Exception\ServerStateConflictException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use function Amp\async;
use function Psl\invariant;

/**
 * @psalm-suppress MissingThrowsDocblock
 */
final class Server implements ServerInterface
{
    private Status $status = Status::Stopped;

    private Dsn\DsnInterface|null $dsn = null;

    private Socket\ResourceServerSocket|null $server = null;

    /**
     * @var array<non-empty-string, Socket\ResourceSocket>
     */
    private array $clients = [];

    /**
     * @var Queue<non-empty-string>
     */
    private Queue $queue;

    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    )
    {
        /** @var Queue<non-empty-string> */
        $this->queue = new Queue();
    }

    public function start(string $address): void
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
            $dsn = Dsn\from_string($address);
            $this->logger->notice(sprintf('Starting broadcast server at address %s', $dsn->toString()));

            $this->server = $this->buildServer($dsn);

            async(function () {
                while ($client = $this->server?->accept()) {
                    $this->onClientConnect($client);
                }
            });

            async(function () {
                foreach ($this->queue->iterate() as $message) {
                    $this->broadcast($message);
                }
            });

            $this->status = Status::Started;
            $this->dsn = $dsn;
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

        invariant(null !== $server = $this->server, 'There must be a server');

        try {
            $this->queue->complete();

            foreach ($this->clients as $client) {
                $this->logger->info('Closing connection '.$client->getLocalAddress()->toString());
                $client->end();
            }

            $server->close();

            $this->logger->notice('Server stopped successfully.');
        } catch (Throwable $exception) {
             $this->logger->error('Error while stopping server.', [
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $this->status = Status::Stopped;
            $this->clients = [];

            if ($this->dsn instanceof Dsn\Unix) {
                unlink($this->dsn->path);
            }

            $this->server = null;
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

    private function buildServer(Dsn\DsnInterface $dsn): Socket\ResourceServerSocket
    {
        if ($dsn instanceof Dsn\Unix) {
            $address = new Socket\UnixAddress($dsn->path);
        } elseif ($dsn instanceof Dsn\Tcp) {
            $address = new Socket\InternetAddress($dsn->host, $dsn->port);
        } else {
            throw new InvalidArgumentException('Unexpected invalid "'.$dsn::class.'" instance');
        }

        return Socket\listen($address);
    }

    private function onClientClose(string $clientId, Socket\ResourceSocket $client): void
    {
        unset($this->clients[$clientId]);
        $this->logger->notice('Client disconnected', ['localAddress' => $client->getLocalAddress()]);
    }

    private function onClientConnect(Socket\ResourceSocket $client): void
    {
        $this->logger->notice('New client connected', ['localAddress' => $client->getLocalAddress()]);

        $clientId = uniqid('', true);
        $this->clients[$clientId] = $client;

        async(function () use ($client) {
            foreach ($client->getIterator() as $message) {
                if ('' !== $message) {
                    $this->queue->pushAsync($message);
                }
            }
        });

        $client->onClose(fn () => $this->onClientClose($clientId, $client));
    }
}
