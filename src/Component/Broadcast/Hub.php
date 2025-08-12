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

use Neu\Component\Broadcast\Exception\AlreadyListeningException;
use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\Broadcast\Transport\TransportInterface;

final class Hub implements HubInterface
{
    /**
     * The transport used to send and receive messages.
     *
     * @var TransportInterface
     */
    private TransportInterface $transport;

    /**
     * The channels created by the hub.
     *
     * @var array<non-empty-string, Channel>
     */
    private array $channels = [];

    /**
     * Create a new {@see Hub} instance.
     *
     * @param TransportInterface $transport The transport to use.
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getChannel(string $name): Channel
    {
        try {
            return $this->channels[$name] ??= new Channel($name, $this->transport);
        } catch (AlreadyListeningException $e) {
            throw new RuntimeException(
                message: 'Failed to create channel as transport is already listening.',
                previous: $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        foreach ($this->channels as $channel) {
            $channel->close();
        }

        $this->channels = [];
    }
}
