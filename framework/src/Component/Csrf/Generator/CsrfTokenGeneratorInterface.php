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
