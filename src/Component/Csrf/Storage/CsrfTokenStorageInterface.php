<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\Storage;

use Neu\Component\Csrf\Exception\RuntimeException;
use Neu\Component\Csrf\Exception\TokenNotFoundException;
use Neu\Component\Http\Message\RequestInterface;
use SensitiveParameter;

/**
 * Interface for storing and retrieving CSRF tokens.
 */
interface CsrfTokenStorageInterface
{
    /**
     * Checks if a CSRF token exists for the given request and identifier.
     *
     * @param RequestInterface $request The request to check for the token.
     * @param non-empty-string $identifier The unique identifier for the token.
     *
     * @throws RuntimeException If the token cannot be checked.
     *
     * @return bool True if the token exists, false otherwise.
     */
    public function hasToken(RequestInterface $request, string $identifier): bool;

    /**
     * Retrieves the value of a CSRF token for the given request and identifier.
     *
     * @param RequestInterface $request The request to retrieve the token value from.
     * @param non-empty-string $identifier The unique identifier for the token.
     *
     * @throws TokenNotFoundException If no token exists for the given identifier.
     * @throws RuntimeException If the token cannot be retrieved.
     *
     * @return non-empty-string The CSRF token value.
     */
    public function getToken(RequestInterface $request, string $identifier): string;

    /**
     * Stores a CSRF token for the given request.
     *
     * @param RequestInterface $request The request to associate the token with.
     * @param non-empty-string $identifier The unique identifier for the token.
     * @param non-empty-string $value The CSRF token value.
     *
     * @throws RuntimeException If the token cannot be set.
     */
    public function setToken(RequestInterface $request, string $identifier, #[SensitiveParameter] string $value): void;

    /**
     * Remove the value of a CSRF token for the given request and identifier.
     *
     * @param RequestInterface $request The request to remove the token value from.
     * @param non-empty-string $identifier The unique identifier for the token.
     *
     * @throws RuntimeException If the token cannot be removed.
     */
    public function removeToken(RequestInterface $request, string $identifier): void;

    /**
     * Clear all CSRF tokens associated with the given request.
     *
     * @param RequestInterface $request The request to remove tokens from.
     *
     * @throws RuntimeException If the tokens cannot be cleared.
     */
    public function clear(RequestInterface $request): void;
}
