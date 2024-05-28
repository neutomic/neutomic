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

final class InvalidKeyException extends InvalidArgumentException
{
    /**
     * Create an exception for a cache key that is too long.
     */
    public static function forLongKey(string $key, int $maximumLength): self
    {
        return new self('Cache key is too long: ' . $key . ' (maximum length: ' . ((string) $maximumLength) . ' characters).');
    }
}
