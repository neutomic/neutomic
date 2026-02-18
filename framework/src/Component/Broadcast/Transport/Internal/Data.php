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

/**
 * An internal data class used to store the channel and message.
 *
 * @internal
 */
final readonly class Data
{
    public string $channel;
    public mixed $message;

    public function __construct(string $channel, mixed $message)
    {
        $this->channel = $channel;
        $this->message = $message;
    }
}
