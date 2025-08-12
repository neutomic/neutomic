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

namespace Neu\Component\Broadcast\Transport\Internal;

use Amp;
use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Amp\Sync\Channel;
use Amp\Sync\ChannelException;
use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\Broadcast\Transport\LocalTransport;
use Neu\Component\Broadcast\Transport\MemoryTransport;
use Neu\Component\Broadcast\Transport\TransportInterface;

/**
 * A transport mechanism that sends and receives messages using channels.
 *
 * This transport is internal, and should not be used directly, use {@see MemoryTransport} or {@see LocalTransport} instead.
 *
 * @internal
 */
final class ChannelTransport implements TransportInterface
{
    /**
     * The sender channel, used to send messages.
     */
    private Channel $sender;

    /**
     * The receiver channel, used to receive messages.
     */
    private Channel $receiver;

    /**
     * @var array<non-empty-string, Queue<mixed>>
     */
    private array $listeners = [];

    /**
     * Creates a new channel transport.
     *
     * @param Channel $sender The sender channel, used to send messages.
     * @param Channel $receiver The receiver channel, used to receive messages.
     */
    public function __construct(Channel $sender, Channel $receiver)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;

        Amp\async(function (): void {
            while (true) {
                try {
                    $data = $this->receiver->receive();
                    if (!$data instanceof Data) {
                        continue;
                    }

                    if (isset($this->listeners[$data->channel])) {
                        try {
                            $this->listeners[$data->channel]->push($data->message);
                        } catch (Amp\Pipeline\DisposedException) {
                            // The queue was disposed, we can remove the listener
                            unset($this->listeners[$data->channel]);
                        }
                    }
                } catch (ChannelException) {
                    // Channel was closed, we can break the loop
                    break;
                }
            }
        })->ignore();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function send(string $channel, mixed $message): void
    {
        try {
            $this->sender->send(new Data($channel, $message));
        } catch (ChannelException $e) {
            throw ClosedTransportException::whileAttemptingToSend($channel, $e);
        } catch (Amp\Serialization\SerializationException $e) {
            throw new RuntimeException('Failed to serialize message for channel "' . $channel . '".', previous: $e);
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
        if (isset($this->listeners[$channel])) {
            throw AlreadyListeningException::forChannel($channel);
        }

        $queue = new Queue();
        /** @var non-empty-string $channel */
        $this->listeners[$channel] = $queue;

        return $queue->iterate();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        foreach ($this->listeners as $queue) {
            $queue->complete();
        }

        $this->listeners = [];

        $this->sender->close();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->sender->isClosed();
    }
}
