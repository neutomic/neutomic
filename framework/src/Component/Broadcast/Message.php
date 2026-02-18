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

/**
 * Represents a message that is broadcasted to a channel.
 *
 * @template T
 */
final readonly class Message
{
    /**
     * The channel to which the message is broadcasted to.
     *
     * @var non-empty-string
     */
    private string $channel;

    /**
     * The payload of the message.
     *
     * @var T
     */
    private mixed $payload;

    /**
     * Create a new {@see \Neu\Component\Broadcast\Transport\Internal\Data} instance.
     *
     * @param non-empty-string $channel The channel to which the message is broadcasted to.
     * @param T $payload The payload of the message.
     */
    public function __construct(string $channel, mixed $payload)
    {
        $this->channel = $channel;
        $this->payload = $payload;
    }

    /**
     * Get the channel to which the message is broadcasted to.
     *
     * @return non-empty-string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * Get the payload of the message.
     *
     * @return T
     */
    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
