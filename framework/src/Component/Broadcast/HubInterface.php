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

use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Broadcast\Exception\RuntimeException;

interface HubInterface
{
    /**
     * Get a channel by name.
     *
     * @template T
     *
     * @param non-empty-string $name The name of the channel.
     *
     * @throws ClosedTransportException If the transport has been closed.
     * @throws RuntimeException If an error occurs while creating the channel.
     *
     * @return ChannelInterface<T> The created channel.
     */
    public function getChannel(string $name): ChannelInterface;

    /**
     * Close the hub, releasing all resources, and closing all channels.
     */
    public function close(): void;
}
