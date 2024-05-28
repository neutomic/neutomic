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

namespace Neu\Component\Broadcast\Exception;

use Throwable;

final class ClosedTransportException extends LogicException implements ExceptionInterface
{
    /**
     * Create a new instance of the exception while attempting to send a message to a channel.
     *
     * @param non-empty-string $channel The channel that the message was attempted to be sent to.
     */
    public static function whileAttemptingToSend(string $channel, null|Throwable $previous = null): self
    {
        return new self('Failed to send message to channel "' . $channel . '" because the transport is closed.', previous: $previous);
    }

    /**
     * Create a new instance of the exception while attempting to listen to a channel.
     *
     * @param non-empty-string $channel The channel that the message was attempted to be listened to.
     */
    public static function whileAttemptingToListen(string $channel, null|Throwable $previous = null): self
    {
        return new self('Failed to listen to channel "' . $channel . '" because the transport is closed.', previous: $previous);
    }
}
