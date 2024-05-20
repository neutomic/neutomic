<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

use Amp\ByteStream\Payload;
use Amp\ByteStream\ReadableIterableStream;
use Amp\ByteStream\ReadableStream;
use Amp\Sync\LocalSemaphore;
use Amp\Sync\Semaphore;
use Amp\TimeoutCancellation;
use Amp\TimeoutException as AmpTimeoutException;
use Closure;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Exception\TimeoutException;
use Traversable;

final class RequestBody implements RequestBodyInterface
{
    private BodyMode $mode;
    private Payload $payload;
    private null|Closure $upgradeSize;
    private Semaphore $semaphore;

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
        $this->semaphore = new LocalSemaphore(1);

        // Set the mode to closed when the payload is closed.
        $payload->onClose(function () {
            $this->mode = BodyMode::Closed;
        });
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
    public function upgradeSizeLimit(int $sizeLimit): void
    {
        if ($this->upgradeSize !== null) {
            ($this->upgradeSize)($sizeLimit);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMode(): BodyMode
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function getChunk(?float $timeout = null): ?string
    {
        if ($this->mode === BodyMode::Closed) {
            throw new RuntimeException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new RuntimeException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->semaphore->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->read();
            }

            try {
                return $this->payload->read(new TimeoutCancellation($timeout));
            } catch (AmpTimeoutException $e) {
                throw new TimeoutException('Reading from the body timed out', 0, $e);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function getContents(?float $timeout = null): string
    {
        if ($this->mode === BodyMode::Buffered) {
            throw new RuntimeException('Cannot buffer a body more than once');
        }

        if ($this->mode === BodyMode::Closed) {
            throw new RuntimeException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Streamed) {
            throw new RuntimeException('Cannot read from a streamed body as a whole');
        }


        $this->mode = BodyMode::Buffered;

        $lock = $this->semaphore->acquire();
        try {
            if (null === $timeout) {
                return $this->payload->buffer();
            }

            try {
                return $this->payload->buffer(new TimeoutCancellation($timeout));
            } catch (AmpTimeoutException $e) {
                throw new TimeoutException('Reading from the body timed out', 0, $e);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        if ($this->mode === BodyMode::Closed) {
            throw new RuntimeException('Cannot read from a closed body');
        }

        if ($this->mode === BodyMode::Buffered) {
            throw new RuntimeException('Cannot read from a buffered body as a chunk');
        }

        $this->mode = BodyMode::Streamed;

        $lock = $this->semaphore->acquire();
        try {
            while (null !== $chunk = $this->payload->read()) {
                yield $chunk;
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->payload->close();
    }
}
