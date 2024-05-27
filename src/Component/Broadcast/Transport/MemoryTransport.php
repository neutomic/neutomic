<?php

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Sync;

/**
 * A transport mechanism that sends and receives messages in memory.
 */
final class MemoryTransport implements TransportInterface
{
    private TransportInterface $transport;

    /**
     * Creates a new memory transport.
     */
    public function __construct()
    {
        [$sender, $receiver] = Sync\createChannelPair();

        $this->transport = new Internal\ChannelTransport($sender, $receiver);
    }

    /**
     * @inheritdoc
     */
    public function send(string $channel, mixed $message): void
    {
        $this->transport->send($channel, $message);
    }

    /**
     * @inheritdoc
     */
    public function isListening(string $channel): bool
    {
        return $this->transport->isListening($channel);
    }

    /**
     * @inheritdoc
     */
    public function listen(string $channel): ConcurrentIterator
    {
        return $this->transport->listen($channel);
    }

    /**
     * @inheritdoc
     */
    public function close(): void
    {
        $this->transport->close();
    }

    /**
     * @inheritdoc
     */
    public function isClosed(): bool
    {
        return $this->transport->isClosed();
    }
}
