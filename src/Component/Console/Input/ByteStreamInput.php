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

namespace Neu\Component\Console\Input;

use Amp\ByteStream;
use Neu\Component\Console\Exception\NonInteractiveInputException;
use Psl\Str;

use const PHP_EOL;

/**
 * A {@see InputInterface} implementation based on {@see ByteStream\ReadableStream}.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class ByteStreamInput extends AbstractInput
{
    /**
     * Buffered reader for user input.
     */
    private ByteStream\BufferedReader $reader;

    /**
     * The original stream.
     */
    private ByteStream\ReadableStream $stream;

    /**
     * Construct a new instance of {@see ByteStreamInput}.
     *
     * @param list<string> $args
     */
    public function __construct(ByteStream\ReadableStream $stream, array $args)
    {
        parent::__construct($args);

        $this->stream = $stream;
        $this->reader = new ByteStream\BufferedReader($stream);
    }

    /**
     * @inheritDoc
     */
    public function getUserInput(null|int $length = null): string
    {
        if (!$this->isInteractive()) {
            throw new NonInteractiveInputException('The current terminal session is non interactive.');
        }

        if ($length !== null) {
            return $this->reader->readLength($length);
        }

        try {
            // Try reading a line.
            $result = $this->reader->readUntil(PHP_EOL);
        } catch (ByteStream\BufferException $e) {
            // If EOF is reached before a line terminator is found, return the buffer.
            $result = $e->getBuffer();
        }

        return Str\trim($result);
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        if ($this->stream instanceof ByteStream\ResourceStream) {
            return $this->stream->getResource();
        }

        return null;
    }
}
