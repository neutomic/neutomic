<?php

declare(strict_types=1);

namespace Neu\Component\Csrf;

use Neu\Component\Csrf\Exception\RuntimeException;
use Neu\Component\Csrf\Exception\TokenNotFoundException;
use Neu\Component\Http\Message\RequestInterface;
use SensitiveParameter;

/**
 * Interface for managing CSRF tokens.
 *
 * This service provides methods to generate, store, retrieve, validate, and remove CSRF tokens,
 * which are used to protect against Cross-Site Request Forgery attacks.
 */
interface CsrfTokenManagerInterface
{
    /**
     * Gets a CSRF token for the given request and identifier.
     *
     * If no token exists for the identifier, a {@see TokenNotFoundException} MUST be thrown.
     *
     * @param RequestInterface $request The request associated with the token.
     * @param non-empty-string $identifier A unique identifier for the token.
     *
     * @throws TokenNotFoundException If no token exists for the given identifier.
     * @throws RuntimeException If an error occurs while retrieving the token.
     *
     * @return non-empty-string The CSRF token value.
     */
    public function getToken(RequestInterface $request, string $identifier): string;

    /**
     * Gets a CSRF token for the given request and identifier.
     *
     * If no token exists for the identifier, a new one is generated and stored.
     *
     * @param RequestInterface $request The request associated with the token.
     * @param non-empty-string $identifier A unique identifier for the token.
     *
     * @throws RuntimeException If an error occurs while generating or storing the token.
     *
     * @return non-empty-string The CSRF token value.
     */
    public function getOrCreateToken(RequestInterface $request, string $identifier): string;

    /**
     * Refreshes an existing CSRF token and returns the new value.
     *
     * This method generates a new token value for the given identifier and replaces the existing one
     * in the storage. It is useful for scenarios where you want to prevent replay attacks by
     * invalidating the old token.
     *
     * @param RequestInterface $request The request associated with the token.
     * @param non-empty-string $identifier A unique identifier for the token.
     *
     * @throws RuntimeException If an error occurs while generating or storing the token.
     *
     * @return non-empty-string The new CSRF token value.
     */
    public function rotateToken(RequestInterface $request, string $identifier): string;

    /**
     * Removes a CSRF token from storage.
     *
     * This method invalidates the token, preventing it from being used in subsequent requests.
     *
     * @param RequestInterface $request The request associated with the token.
     * @param non-empty-string $identifier A unique identifier for the token.
     *
     * @throws RuntimeException If an error occurs while removing the token.
     */
    public function removeToken(RequestInterface $request, string $identifier): void;

    /**
     * Validates a CSRF token value against the stored token for the given request and identifier.
     *
     * @param RequestInterface $request The request to validate the token against.
     * @param non-empty-string $identifier The unique identifier for the token.
     * @param non-empty-string $value The CSRF token value to validate.
     *
     * @throws RuntimeException If an error occurs while validating the token.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function validateToken(RequestInterface $request, string $identifier, #[SensitiveParameter] string $value): bool;
}
