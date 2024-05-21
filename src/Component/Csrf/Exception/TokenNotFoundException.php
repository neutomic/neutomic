<?php

declare(strict_types=1);

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
