<?php

declare(strict_types=1);

namespace Neu\Component\Cache\Exception;

use InvalidArgumentException;

final class InvalidKeyException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * Create an exception for an empty cache key.
     */
    public static function forEmptyKey(): self
    {
        return new self('Cache key must not be empty.');
    }

    /**
     * Create an exception for a cache key that is too long.
     */
    public static function forLongKey(string $key, int $maximumLength): self
    {
        return new self('Cache key is too long: ' . $key . ' (maximum length: ' . $maximumLength . ' characters).');
    }
}
