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

namespace Neu\Component\Database\Notification;

final readonly class Notification
{
    /**
     * @param string $channel The channel identifier
     * @param string $payload The message payload
     * @param int<1, max> $pid The process id of the message source.
     */
    public function __construct(
        public string $channel,
        public string $payload,
        public int    $pid,
    ) {
    }
}
