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

use Iterator;

interface ListenerInterface
{
    /**
     * Retrieve the channel name that is used for receiving notifications.
     *
     * @return non-empty-string
     */
    public function getChannel(): string;

    /**
     * @return Iterator<int, Notification>
     */
    public function listen(): Iterator;

    /**
     * Check whether the listener is still able to receive notifications.
     */
    public function isAlive(): bool;

    /**
     * Close the listener.
     *
     * After the listener is closed, no more notifications will be received.
     */
    public function close(): void;
}
