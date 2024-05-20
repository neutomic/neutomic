<?php

declare(strict_types=1);

namespace Neu\Component\Database\Exception;

use Throwable;

final class DatabaseNotFoundException extends RuntimeException
{
    /**
     * Create an exception for the default database that is not configured.
     */
    public static function forDefaultDatabase(): self
    {
        return new self('The default database is not configured.');
    }

    /**
     * Create an exception for a database that is not found by its name.
     *
     * @param non-empty-string $name
     */
    public static function forDatabase(string $name, ?Throwable $previous = null): self
    {
        return new self('Database "' . $name . '" was not found.', previous: $previous);
    }
}
