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

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\DisposedException;
use Amp\Pipeline\Pipeline;
use Amp\Pipeline\Queue;
use IteratorAggregate;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;

/**
 * Represents a stream of Server-Sent Events (SSE).
 *
 * @implements IteratorAggregate<int, Event>
 */
final readonly class EventStream implements IteratorAggregate
{
    private const int DEFAULT_BUFFER_SIZE = 0;

    /**
     * The queue of events to be sent to the client.
     *
     * @var Queue<Event>
     */
    private Queue $queue;

    /**
     * Creates a new instance of {@see EventStream}.
     *
     * @param int<0, max> $bufferSize The size of the event buffer.
     */
    public function __construct(int $bufferSize = self::DEFAULT_BUFFER_SIZE)
    {
        /** @psalm-suppress MixedPropertyTypeCoercion */
        $this->queue = new Queue($bufferSize);
    }

    /**
     * Creates an instance of {@see EventStream} for a specific {@see Context}.
     *
     * Registers a callback to close the stream when the client disconnects.
     *
     * @param Context $context The HTTP runtime context.
     * @param int<0, max> $bufferSize The size of the event buffer.
     *
     * @return self A new instance of EventStream.
     */
    public static function forContext(Context $context, int $bufferSize = self::DEFAULT_BUFFER_SIZE): self
    {
        $stream = new self($bufferSize);

        $context->getClient()->onClose(static fn () => $stream->close());

        return $stream;
    }

    /**
     * Sends an event to the stream.
     *
     * @param Event $event The event to send.
     *
     * @throws Exception\StreamClosedException If the stream is already closed.
     * @throws Exception\StreamIteratorDisposedException If the stream iterator is disposed.
     */
    public function send(Event $event): void
    {
        if ($this->isClosed()) {
            throw new Exception\StreamClosedException('The event stream has been closed.');
        }

        try {
            $this->queue->push($event);
        } catch (DisposedException $e) {
            $this->close();

            throw new Exception\StreamIteratorDisposedException('The event stream iterator has been disposed.', previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Gets an iterator for the events in the stream.
     *
     * @return ConcurrentIterator<Event> An iterator over the queued events.
     */
    public function getIterator(): ConcurrentIterator
    {
        return $this->queue->iterate();
    }

    /**
     * Gets the response representing the SSE stream.
     *
     * Sets the appropriate headers for SSE and the body to the event iterator.
     *
     * @return ResponseInterface The HTTP response for the SSE stream.
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function getResponse(): ResponseInterface
    {
        return Response::fromStatusCode(200)
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('Connection', 'keep-alive')
            ->withBody(Body::fromIterable(
                Pipeline::fromIterable($this->getIterator())
                    ->sequential()
                    ->map(static fn (Event $event): string => $event->toString())
                    ->getIterator()
            ));
    }

    /**
     * Checks if the stream is closed.
     *
     * @return bool True if the stream is closed, false otherwise.
     */
    public function isClosed(): bool
    {
        return $this->queue->isComplete();
    }

    /**
     * Closes the stream.
     *
     * Marks the event queue as complete, stopping any further events from being sent.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            return;
        }

        $this->queue->complete();
    }
}
