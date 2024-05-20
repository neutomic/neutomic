<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

use IteratorAggregate;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Exception\TimeoutException;
use Traversable;

/**
 * Interface for HTTP message bodies supporting various modes of operation, such as chunked reading and buffering.
 *
 * The state of the body is managed through the BodyMode enum, reflecting different stages of interaction.
 *
 * @extends IteratorAggregate<string>
 */
interface BodyInterface extends IteratorAggregate
{
    /**
     * Returns the current mode of the body.
     *
     * @return BodyMode The current mode of the body.
     */
    public function getMode(): BodyMode;

    /**
     * Reads a chunk of data from the body.
     *
     * If this method is called, the body mode is set to {@see BodyMode::Streamed},
     * unless it's already in {@see BodyMode::Streamed} mode or {@see BodyMode::Closed}.
     *
     * If the body mode is {@see BodyMode::Buffered}, a {@see RuntimeException} is thrown.
     *
     * @param float|null $timeout Optional timeout for the read operation.
     *
     * @throws TimeoutException if the read operation times out.
     * @throws RuntimeException if the body mode is {@see BodyMode::Buffered}, {@see BodyMode::Closed}, or an error occurs.
     *
     * @return string|null Returns the chunk of data read, or null if at the end of the data source.
     */
    public function getChunk(?float $timeout = null): ?string;

    /**
     * Reads the entire contents of the body into a buffer.
     *
     * Once called, the body mode is set to {@see BodyMode::Buffered} unless it is already {@see BodyMode::Closed}.
     *
     * If the body mode is not {@see BodyMode::None}, a {@see RuntimeException} is thrown to prevent switching from
     * {@see BodyMode::Streamed} or re-reading in {@see BodyMode::Buffered} mode, or reading a closed body.
     *
     * @param float|null $timeout Optional timeout for the read operation.
     *
     * @throws TimeoutException if the read operation times out.
     * @throws RuntimeException if the body mode is {@see BodyMode::Streamed}, {@see BodyMode::Buffered}, {@see BodyMode::Closed},
     *                          or an error occurs.
     *
     * @return string The entire contents of the body.
     */
    public function getContents(?float $timeout = null): string;

    /**
     * Returns an iterator for the body content.
     *
     * This method is used to iterate over the body content in a streaming fashion.
     *
     * @return Traversable<int, string> An iterator for the body content.
     */
    public function getIterator(): Traversable;

    /**
     * Closes the body, setting its mode to {@see BodyMode::Closed} and preventing any further read operations.
     *
     * Once closed, any read attempts will throw a {@see RuntimeException}.
     */
    public function close(): void;
}
