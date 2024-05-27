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

namespace Neu\Component\Broadcast;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\DisposedException;
use Amp\Pipeline\Queue;
use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\ClosedChannelException;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\Broadcast\Transport\TransportInterface;
use Revolt\EventLoop;

use function count;

/**
 * @template T
 *
 * @implements ChannelInterface<T>
 */
final class Channel implements ChannelInterface
{
    /**
     * The next subscription ID.
     *
     * @var int
     */
    private int $nextId = 0;

    /**
     * The name of the channel.
     *
     * @var non-empty-string
     */
    private string $name;

    /**
     * The transport used to send and receive messages.
     */
    private TransportInterface $transport;

    /**
     * The iterator used to listen for messages.
     *
     * @var null|ConcurrentIterator
     */
    private null|ConcurrentIterator $iterator;

    /**
     * The queues to which messages are broadcasted.
     *
     * @var array<int, Queue<Message<T>>>
     */
    private array $queues = [];

    /**
     * Create a new {@see Channel} instance.
     *
     * @param non-empty-string $name The name of the channel.
     * @param TransportInterface $transport The transport used to send and receive messages.
     *
     * @throws RuntimeException If an error occurs while listening for messages.
     * @throws AlreadyListeningException If messages from the given channel are already being listened to.
     * @throws ClosedTransportException If the transport has been closed.
     */
    public function __construct(string $name, TransportInterface $transport)
    {
        $this->name = $name;
        $this->transport = $transport;
        $this->iterator = $this->transport->listen($this->name);

        EventLoop::defer(function (): void {
            $iterator = $this->iterator;
            if ($iterator === null) {
                return;
            }

            try {
                while ($iterator->continue()) {
                    /** @var T $message */
                    $message = $iterator->getValue();

                    $queues = $this->queues;
                    foreach ($queues as $id => $queue) {
                        try {
                            $queue->push(new Message($this->name, $message));
                        } catch (DisposedException) {
                            // Remove disposed queue
                            unset($this->queues[$id]);
                        }
                    }
                }
            } catch (DisposedException) {
                // Ignore
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function broadcast(mixed $message): void
    {
        if ($this->iterator === null) {
            throw new ClosedChannelException('The channel "' . $this->name . '" has been closed.');
        }

        try {
            $this->transport->send($this->name, $message);
        } catch (ClosedTransportException $e) {
            $this->close();

            throw new ClosedChannelException('The channel "' . $this->name . '" has been closed.', previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function subscribe(): Subscription
    {
        if ($this->iterator === null) {
            throw new ClosedChannelException('The channel "' . $this->name . '" has been closed.');
        }

        $id = $this->nextId++;
        /** @var Queue<Message<T>> $queue */
        $queue = new Queue();
        $this->queues[$id] = $queue;

        return new Subscription($this->name, $queue->iterate(), function () use ($queue, $id) {
            unset($this->queues[$id]);

            $queue->complete();
        });
    }

    /**
     * @inheritDoc
     */
    public function getSubscribersCount(): int
    {
        return count($this->queues);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->iterator === null) {
            return;
        }

        $this->iterator->dispose();
        $this->iterator = null;
    }
}
