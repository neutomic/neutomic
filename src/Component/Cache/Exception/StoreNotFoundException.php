<?php

declare(strict_types=1);

namespace Neu\Component\Cache\Exception;

use Throwable;

final class StoreNotFoundException extends RuntimeException
{
    /**
     * Create an exception for the default store that is not configured.
     */
    public static function forDefaultStore(): self
    {
        return new self('The default store is not configured.');
    }

    /**
     * Create an exception for a store that is not found by its identifier.
     *
     * @param non-empty-string $identifier
     */
    public static function forStore(string $identifier, ?Throwable $previous = null): self
    {
        return new self('Store "' . $identifier . '" was not found.', previous: $previous);
    }
}
