<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

/**
 * Enumeration of possible states for an HTTP message body.
 *
 * - None: Initial state before any operations are performed.
 * - Buffered: The body is in a buffered state, typically after the entire contents have been read.
 * - Streamed: The body is being read incrementally in chunks.
 * - Closed: The body has been closed and is no longer readable.
 */
enum BodyMode: string
{
    case None = 'none';
    case Buffered = 'buffered';
    case Streamed = 'streamed';
    case Closed = 'closed';

    public function isStreamable(): bool
    {
        return $this === self::None || $this === self::Streamed;
    }
}
