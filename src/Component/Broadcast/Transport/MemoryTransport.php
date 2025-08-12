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
     * @inheritDoc
     */
    #[\Override]
    public function send(string $channel, mixed $message): void
    {
        $this->transport->send($channel, $message);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isListening(string $channel): bool
    {
        return $this->transport->isListening($channel);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function listen(string $channel): ConcurrentIterator
    {
        return $this->transport->listen($channel);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        $this->transport->close();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->transport->isClosed();
    }
}
