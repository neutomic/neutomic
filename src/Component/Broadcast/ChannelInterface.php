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

use Neu\Component\Broadcast\Exception\ClosedChannelException;
use Neu\Component\Broadcast\Exception\RuntimeException;

/**
 * Represents a channel to which messages can be broadcasted to.
 *
 * @template T
 */
interface ChannelInterface
{
    /**
     * Broadcast a message to the channel.
     *
     * @param T $message The message to broadcast.
     *
     * @throws ClosedChannelException If the channel has been closed.
     * @throws RuntimeException If an error occurs while broadcasting the message.
     */
    public function broadcast(mixed $message): void;

    /**
     * Subscribe to the channel to receive messages.
     *
     * @throws ClosedChannelException If the channel has been closed.
     * @throws RuntimeException If an error occurs while subscribing to the channel.
     *
     * @return Subscription<T> The subscription to the channel.
     */
    public function subscribe(): Subscription;

    /**
     * Get the number of subscribers to the channel.
     *
     * @return int The number of subscribers to the channel.
     */
    public function getSubscribersCount(): int;

    /**
     * Close the channel, releasing all resources, and canceling all subscriptions.
     */
    public function close(): void;
}
