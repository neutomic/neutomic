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

namespace Neu\Component\Http\ServerSentEvent;

/**
 * Represents a Server-Sent Event.
 */
final readonly class Event
{
    /**
     * The data of the event.
     */
    public string $data;

    /**
     * The type of event.
     *
     * @var non-empty-string|null
     */
    public null|string $type;

    /**
     * The ID of the event.
     *
     * @var non-empty-string|null
     */
    public null|string $id;

    /**
     * The reconnection time in milliseconds.
     *
     * @var positive-int|null
     */
    public null|int $retry;

    /**
     * Creates a new instance of {@see Event}.
     *
     * @param string $data The data of the event.
     * @param non-empty-string|null $type The type of event.
     * @param non-empty-string|null $id The ID of the event.
     * @param positive-int|null $retry The reconnection time in milliseconds.
     */
    public function __construct(string $data = '', null|string $type = null, null|string $id = null, null|int $retry = null)
    {
        $this->data = $data;
        $this->type = $type;
        $this->id = $id;
        $this->retry = $retry;
    }

    /**
     * Creates a new instance with the specified data.
     *
     * @param string $data The data of the event.
     *
     * @return self A new instance with the specified data.
     */
    public function withData(string $data): self
    {
        return new self($data, $this->type, $this->id, $this->retry);
    }

    /**
     * Creates a new instance with the specified event type.
     *
     * @param null|non-empty-string $type The type of event.
     *
     * @return self A new instance with the specified event type.
     */
    public function withType(null|string $type): self
    {
        return new self($this->data, $type, $this->id, $this->retry);
    }

    /**
     * Creates a new instance with the specified ID.
     *
     * @param null|non-empty-string $id The ID of the event.
     *
     * @return self A new instance with the specified ID.
     */
    public function withId(null|string $id): self
    {
        return new self($this->data, $this->type, $id, $this->retry);
    }

    /**
     * Creates a new instance with the specified retry time.
     *
     * @param null|positive-int $retry The reconnection time in milliseconds.
     *
     * @return self A new instance with the specified retry time.
     */
    public function withRetry(null|int $retry): self
    {
        return new self($this->data, $this->type, $this->id, $retry);
    }

    /**
     * Converts the {@see Event} instance to a string in the SSE format.
     *
     * @return non-empty-string The event as a formatted string.
     */
    public function toString(): string
    {
        $type = $this->type !== null ? "event: $this->type\n" : '';
        $data = "data: $this->data\n";
        $id = $this->id !== null ? "id: $this->id\n" : '';
        $retry = $this->retry !== null ? "retry: $this->retry\n" : '';

        return "$type$data$id$retry\n";
    }

    /**
     * Alias for {@see toString()}.
     *
     * @return non-empty-string The event as a formatted string.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
