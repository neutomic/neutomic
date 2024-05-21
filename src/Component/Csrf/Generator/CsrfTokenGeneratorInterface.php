<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\Generator;

use Neu\Component\Csrf\Exception\RuntimeException;

/**
 * A token generator that creates cryptographically secure CSRF tokens.
 */
interface CsrfTokenGeneratorInterface
{
    /**
     * Generates a cryptographically secure CSRF token.
     *
     * The generated token MUST be a non-empty string that is URI-safe, meaning it does not contain
     * the characters '+', '=', or '/'.
     *
     * @throws RuntimeException If the token cannot be generated.
     *
     * @return non-empty-string The generated CSRF token.
     */
    public function generate(): string;
}
