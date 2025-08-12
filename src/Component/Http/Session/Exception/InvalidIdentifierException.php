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

use Throwable;

/**
 * This exception is thrown when a session identifier is invalid.
 */
final class InvalidIdentifierException extends InvalidArgumentException
{
    /**
     * Create a new {@see InvalidIdentifierException} for the given identifier.
     *
     * @param non-empty-string $identifier The invalid session identifier.
     * @param null|Throwable $throwable The previous throwable used for the exception chaining.
     *
     * @return self The InvalidIdentifierException instance.
     */
    public static function for(string $identifier, null|Throwable $previous = null): self
    {
        return new self(
            message: 'The session identifier "' . $identifier . '" is invalid.',
            previous: $previous,
        );
    }
}
