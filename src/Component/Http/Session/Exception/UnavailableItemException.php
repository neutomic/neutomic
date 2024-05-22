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

namespace Neu\Component\Http\Session\Exception;

use RuntimeException;

final class UnavailableItemException extends RuntimeException implements ExceptionInterface
{
    /**
     * Create an {@see UnavailableItemException} for the given key.
     *
     * @param non-empty-string $key
     */
    public static function for(string $key): self
    {
        return new self('No session item is associated with the "' . $key . '" key.');
    }
}
