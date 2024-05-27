<?php

namespace Neu\Component\Broadcast\Transport;

use Amp\Sync;
use Amp\Cluster\Cluster;
use Amp\Pipeline\ConcurrentIterator;

/**
 * A local transport mechanism that sends and receives messages based on the current execution context.
 *
 * Unlike {@see MemoryTransport}, this transport is intended for use in a distributed environment ( i.e. multiple workers ).
 */
final readonly class LocalTransport implements TransportInterface
{
    private TransportInterface $transport;

    public function __construct()
    {
        if (Cluster::isWorker()) {
            $channel = Cluster::getChannel();

            $this->transport = new Internal\ChannelTransport($channel, $channel);
        } else {
            [$sender, $receiver] = Sync\createChannelPair();

            $this->transport = new Internal\ChannelTransport($sender, $receiver);
        }
    }

    public function send(string $channel, mixed $message): void
    {
        $this->transport->send($channel, $message);
    }

    public function isListening(string $channel): bool
    {
        return $this->transport->isListening($channel);
    }

    public function listen(string $channel): ConcurrentIterator
    {
        return $this->transport->listen($channel);
    }

    public function close(): void
    {
        $this->transport->close();
    }

    public function isClosed(): bool
    {
        return $this->transport->isClosed();
    }
}
