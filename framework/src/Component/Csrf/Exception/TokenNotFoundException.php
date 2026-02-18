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

namespace Neu\Component\Csrf\Exception;

use Neu\Component\Csrf\CsrfTokenManagerInterface;

/**
 * Exception thrown when a CSRF token was not found.
 *
 * @see CsrfTokenManagerInterface::getToken()
 */
final class TokenNotFoundException extends RuntimeException
{
    /**
     * Creates a new {@see TokenNotFoundException} instance for the given identifier.
     *
     * @param string $identifier The identifier for which the CSRF token was not found.
     */
    public static function forIdentifier(string $identifier): static
    {
        return new static('The CSRF token for the identifier "' . $identifier . '" was not found.');
    }
}
