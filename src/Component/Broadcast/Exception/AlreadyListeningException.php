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

final class AlreadyListeningException extends LogicException
{
    /**
     * Create an exception for a channel that is already being listened to.
     *
     * @param non-empty-string $channel The channel that is already being listened to.
     */
    public static function forChannel(string $channel): self
    {
        return new self('Channel "' . $channel . '" is already being listened to.');
    }
}
