<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use Amp\Socket\Socket;
use Amp;
use Psl;
use Neu\Component\Broadcast\Address\SocketAddressInterface;
use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use function Amp\Socket\connect;

final class SocketTransport implements TransportInterface
{
    private Socket $connection;

    private NativeSerializer|Serializer $serializer;

    /**
     * @var array<non-empty-string, Queue>
     */
    private array $listeners = [];

    public function __construct(Socket $connection, null|Serializer $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new NativeSerializer();
    }

    /**
     * @inheritDoc
     */
    public function send(string $channel, mixed $message): void
    {
        try {
            $serialized = $this->serializer->serialize(new Internal\Data($channel, $message));
        } catch (Amp\Serialization\SerializationException $e) {
            throw new RuntimeException('Failed to serialize message.', previous: $e);
        }

        if ($this->connection->isClosed()) {
            throw new ClosedTransportException('The transport is closed.');
        }

        $this->connection->write($serialized);
    }

    /**
     * @inheritDoc
     */
    public function isListening(string $channel): bool
    {
        return isset($this->listeners[$channel]);
    }

    /**
     * @inheritDoc
     */
    public function listen(string $channel): ConcurrentIterator
    {
        if ($this->connection->isClosed()) {
            throw new ClosedTransportException('The transport is closed.');
        }

        if ($this->isListening($channel)) {
            throw new AlreadyListeningException('Already listening to channel "' . $channel . '".');
        }

        $queue = new Queue();
        $this->listeners[$channel] = $queue;

        Amp\async(function () use ($queue): void {
            while (null !== $notification = $this->connection->read()) {
                /** @var Internal\Data $data */
                $data = $this->serializer->unserialize($notification);

                if (!isset($this->listeners[$data->channel])) {
                    continue;
                }

                $this->listeners[$data->channel]->push($data->message);
            }

            $queue->complete();
        });

        return $queue->iterate();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->connection->isClosed()) {
            return;
        }

        foreach ($this->listeners as $channel => $listener) {
            $listener->complete();
        }

        $this->listeners = [];

        $this->connection->close();
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->connection->isClosed();
    }
}
