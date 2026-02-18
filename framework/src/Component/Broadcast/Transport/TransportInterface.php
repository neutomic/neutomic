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
use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;

/**
 * Interface for a transport mechanism that can send and receive messages on channels.
 */
interface TransportInterface
{
    /**
     * Sends a message to a specified channel.
     *
     * @param non-empty-string $channel The identifier of the channel to send the message to.
     * @param mixed $message The message to send to the channel.
     *
     * @throws ClosedTransportException If the transport has been closed.
     * @throws RuntimeException If an error occurs while sending the message.
     */
    public function send(string $channel, mixed $message): void;

    /**
     * Checks if the transport is listening for messages on a specified channel.
     *
     * @param non-empty-string $channel The identifier of the channel to check.
     *
     * @return bool True if the transport is listening for messages on the channel, false otherwise.
     */
    public function isListening(string $channel): bool;

    /**
     * Listens for incoming messages on a specified channel.
     *
     * @param non-empty-string $channel The identifier of the channel to listen to.
     *
     * @throws AlreadyListeningException If messages from the given channel are already being listened to.
     * @throws ClosedTransportException If the transport has been closed.
     * @throws RuntimeException If an error occurs while listening for messages.
     *
     * @return ConcurrentIterator<mixed> An iterator that yields messages received from the channel.
     */
    public function listen(string $channel): ConcurrentIterator;

    /**
     * Closes the transport, releasing all resources and completing all iterators.
     */
    public function close(): void;

    /**
     * Checks if the transport is closed.
     *
     * @return bool True if the transport is closed, false otherwise.
     */
    public function isClosed(): bool;
}
