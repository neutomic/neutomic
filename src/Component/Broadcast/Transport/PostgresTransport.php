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

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\DisposedException;
use Amp\Pipeline\Queue;
use Amp\Postgres\PostgresConnection;
use Amp\Postgres\PostgresListener;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use Amp;
use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;

final class PostgresTransport implements TransportInterface
{
    private PostgresConnection $connection;
    private Serializer $serializer;

    /**
     * @var array<non-empty-string, PostgresListener>
     */
    private array $listeners = [];

    /**
     * Create a new {@see PostgresTransport} instance.
     *
     * @param PostgresConnection $connection The Postgres connection to use.
     * @param Serializer|null $serializer The serializer to use.
     */
    public function __construct(PostgresConnection $connection, null|Serializer $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new NativeSerializer();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function send(string $channel, mixed $message): void
    {
        try {
            $serialized = $this->serializer->serialize($message);
        } catch (Amp\Serialization\SerializationException $e) {
            throw new RuntimeException('Failed to serialize message.', previous: $e);
        }

        if ($this->connection->isClosed()) {
            throw new ClosedTransportException('The transport is closed.');
        }

        try {
            $this->connection->notify($channel, $serialized);
        } catch (Amp\Sql\SqlException | Amp\Sql\SqlQueryError $e) {
            throw new RuntimeException('Failed to send message to channel "' . $channel . '".', previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isListening(string $channel): bool
    {
        return isset($this->listeners[$channel]);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function listen(string $channel): ConcurrentIterator
    {
        if ($this->connection->isClosed()) {
            throw new ClosedTransportException('The transport is closed.');
        }

        if (isset($this->listeners[$channel])) {
            throw new AlreadyListeningException('Already listening to channel "' . $channel . '".');
        }

        try {
            $listener = $this->connection->listen($channel);
        } catch (Amp\Sql\SqlException | Amp\Sql\SqlQueryError $e) {
            throw new RuntimeException('Failed to listen to channel "' . $channel . '".', previous: $e);
        }

        /** @var non-empty-string $channel */
        $this->listeners[$channel] = $listener;

        $queue = new Queue();

        Amp\async(function () use ($channel, $listener, $queue): void {
            try {
                foreach ($listener as $notification) {
                    /** @var mixed $message */
                    $message = $this->serializer->unserialize($notification->payload);

                    $queue->push($message);
                }

                $queue->complete();
            } catch (DisposedException) {
                // The queue iterator was disposed, so we can stop listening.
                $listener->unlisten();

                unset($this->listeners[$channel]);
            }
        })->ignore();

        return $queue->iterate();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        if ($this->connection->isClosed()) {
            return;
        }

        foreach ($this->listeners as $listener) {
            $listener->unlisten();
        }

        $this->listeners = [];

        $this->connection->close();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->connection->isClosed();
    }
}
