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
use Closure;
use Revolt\EventLoop;

/**
 * Represents a subscription to a channel, providing an iterator to receive messages.
 *
 * @template T
 */
final class Subscription
{
    /**
     * The channel to which the subscription is made.
     *
     * @var non-empty-string
     */
    private string $channel;

    /**
     * The iterator that yields messages from the channel.
     *
     * @var ConcurrentIterator<Message<T>>
     */
    private ConcurrentIterator $iterator;

    /**
     * The release callback to invoke when the subscription is cancelled.
     *
     * @var null|(Closure(): void)
     */
    private null|Closure $release;

    /**
     * Create a new {@see Subscription} instance.
     *
     * @param non-empty-string $channel The channel to which the subscription is made.
     * @param ConcurrentIterator<Message<T>> $iterator The iterator that yields messages from the channel.
     * @param (Closure(): void) $release The release callback to invoke when the subscription is cancelled.
     */
    public function __construct(string $channel, ConcurrentIterator $iterator, Closure $release)
    {
        $this->channel = $channel;
        $this->iterator = $iterator;
        $this->release = $release;
    }

    /**
     * Gets the channel associated with the subscription.
     *
     * @return string The channel name.
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * Receives the next message from the channel.
     *
     * @return Message<T>|null The next message, or `null` if the subscription was cancelled.
     */
    public function receive(): null|Message
    {
        if (null === $this->release) {
            // the subscription was cancelled

            return null;
        }

        try {
            if (!$this->iterator->continue()) {
                // the iterator has completed, possibly due to the broadcast channel being closed

                return null;
            }
        } catch (DisposedException) {
            // the iterator was disposed, possibly due to the subscription being cancelled,
            // this should not usually happen because when the subscription is cancelled,
            // `$this->release` would be set to `null`, but we handle it just in case

            return null;
        }

        return $this->iterator->getValue();
    }

    /**
     * Consumes messages from the channel using the provided consumer.
     *
     * @param (Closure(Subscription<T>, Message<T>): void) $consumer The consumer to process messages.
     */
    public function consume(Closure $consumer): void
    {
        EventLoop::defer(function () use ($consumer): void {
            while ($message = $this->receive()) {
                $consumer($this, $message);
            }
        });
    }

    /**
     * Cancels the subscription.
     */
    public function cancel(): void
    {
        if (null === $this->release) {
            return;
        }

        try {
            $release = $this->release;
            $release();
        } finally {
            $this->release = null;
            $this->iterator->dispose();
        }
    }

    /**
     * Cancels the subscription when the instance is destroyed.
     */
    public function __destruct()
    {
        $this->cancel();
    }
}
