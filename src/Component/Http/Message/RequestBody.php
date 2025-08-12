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

namespace Neu\Component\Http\Message;

use Amp\ByteStream\Payload;
use Amp\ByteStream\PendingReadError;
use Amp\ByteStream\ReadableIterableStream;
use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\StreamException;
use Amp\CancelledException;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use Amp\TimeoutCancellation;
use Amp\TimeoutException as AmpTimeoutException;
use Closure;
use Error;
use Neu\Component\Http\Exception\LogicException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Exception\TimeoutException;
use Traversable;

final class RequestBody implements RequestBodyInterface
{
    /**
     * The mode of the body.
     */
    private BodyMode $mode;

    /**
     * The payload of the body.
     */
    private Payload $payload;

    /**
     * The callback to upgrade the size limit of the request body.
     */
    private null|Closure $upgradeSize;

    /**
     * The mutex to synchronize access to the body.
     */
    private Mutex $mutex;

    /**
     * Constructs a new instance of {@see RequestBodyInterface} with a specific {@see Payload}.
     *
     * @param Payload $payload The payload, encapsulating the actual data of the body.
     * @param null|(Closure(int): void) $upgradeSize Optional callback to adjust the size limit of the request body.
     */
    public function __construct(Payload $payload, null|Closure $upgradeSize = null)
    {
        $this->mode = BodyMode::None;
        $this->payload = $payload;
        $this->upgradeSize = $upgradeSize;
        $this->mutex = new LocalMutex();

        if ($payload->isClosed()) {
            $this->mode = BodyMode::Closed;
        }
    }

    /**
     * Factory method to create a {@see RequestBody} from an iterable source.
     *
     * @param iterable<string> $iterable The iterable.
     * @param null|(Closure(int): void) $upgradeSize Optional callback to adjust the size limit of the request body.
     *
     * @return self The new request body.
     */
    public static function fromIterable(iterable $iterable, null|Closure $upgradeSize = null): self
    {
        return new self(new Payload(new ReadableIterableStream($iterable)), $upgradeSize);
    }

    /**
     * Factory method to create a {@see RequestBody} from a readable stream.
     *
     * @param ReadableStream $stream The stream.
     * @param null|(Closure(int): void) $upgradeSize Optional callback to adjust the size limit of the request body.
     *
     * @return self The new request body.
     */
    public static function fromReadableStream(ReadableStream $stream, null|Closure $upgradeSize = null): self
    {
        return new self(new Payload($stream), $upgradeSize);
    }

    /**
     * Factory method to create a {@see RequestBody} from a string.
     *
     * @param string $string The string.
     * @param null|(Closure(int): void) $upgradeSize Optional callback to adjust the size limit of the request body.
     *
     * @return self The new request body.
     */
    public static function fromString(string $string, null|Closure $upgradeSize = null): self
    {
        return new self(new Payload($string), $upgradeSize);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function upgradeSizeLimit(int $sizeLimit): void
    {
        if ($this->upgradeSize !== null) {
            ($this->upgradeSize)($sizeLimit);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMode(): BodyMode
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getChunk(null|float $timeout = null): null|string
    {
        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->mutex->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->read();
            }

            return $this->payload->read(new TimeoutCancellation($timeout));
        } catch (AmpTimeoutException $e) {
            throw new TimeoutException('Reading from the body timed out', 0, $e);
        } catch (StreamException | PendingReadError $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getContents(null|float $timeout = null): string
    {
        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot buffer a body more than once');
        }

        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Streamed) {
            throw new LogicException('Cannot read from a streamed body as a whole');
        }

        $this->mode = BodyMode::Buffered;

        $lock = $this->mutex->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->buffer();
            }

            return $this->payload->buffer(new TimeoutCancellation($timeout));
        } catch (CancelledException $e) {
            throw new TimeoutException('Reading from the body timed out', 0, $e);
        } catch (StreamException | PendingReadError | Error $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getIterator(): Traversable
    {
        if ($this->mode === BodyMode::Closed) {
            throw new LogicException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new LogicException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->mutex->acquire();
        try {
            while (null !== $chunk = $this->payload->read()) {
                yield $chunk;
            }
        } catch (StreamException | PendingReadError $e) {
            throw new RuntimeException('An error occurred while reading from the body', 0, $e);
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        $this->payload->close();
    }

    /**
     * Destructs the body, closing it if it hasn't been closed yet.
     */
    public function __destruct()
    {
        $this->close();
    }
}
