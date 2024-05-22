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
    public static function forDatabase(string $name, null|Throwable $previous = null): self
    {
        return new self('Database "' . $name . '" was not found.', previous: $previous);
    }
}
