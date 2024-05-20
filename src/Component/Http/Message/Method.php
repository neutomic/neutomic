<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

enum Method: string
{
    case Head = 'HEAD';
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
    case Options = 'OPTIONS';
    case Purge = 'PURGE';
    case Trace = 'TRACE';
    case Connect = 'CONNECT';

    /**
     * Returns true if the method is safe.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
     */
    public function isSafe(): bool
    {
        return match ($this) {
            self::Get, self::Head, self::Options, self::Trace => true,
            default => false,
        };
    }

    /**
     * Returns true if the method is idempotent.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
     */
    public function isIdempotent(): bool
    {
        return match ($this) {
            self::Get, self::Head, self::Options, self::Trace, self::Put, self::Delete, self::Post => true,
            default => false,
        };
    }

    /**
     * Returns true if the method is cacheable.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
     */
    public function isCacheable(): bool
    {
        return match ($this) {
            self::Get, self::Head => true,
            default => false,
        };
    }
}
