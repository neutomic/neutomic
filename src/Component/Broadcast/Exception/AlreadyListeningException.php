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

use Neu\Component\Exception\LogicException as RootLogicException;

final class AlreadyListeningException extends RootLogicException implements ExceptionInterface
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
