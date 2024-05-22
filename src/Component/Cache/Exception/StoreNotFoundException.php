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
    public static function forStore(string $identifier, null|Throwable $previous = null): self
    {
        return new self('Store "' . $identifier . '" was not found.', previous: $previous);
    }
}
