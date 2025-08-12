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

namespace Neu\Component\Csrf\Storage;

use Neu\Component\Csrf\Exception\RuntimeException;
use Neu\Component\Csrf\Exception\TokenNotFoundException;
use Neu\Component\Http\Exception\LogicException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Session\Exception\UnavailableItemException;
use Neu\Component\Http\Session\SessionInterface;
use Psl\Str;
use SensitiveParameter;
use Override;

/**
 * A storage implementation that stores CSRF tokens in the session.
 */
final readonly class SessionCsrfTokenStorage implements CsrfTokenStorageInterface
{
    /**
     * The default prefix to use for the session keys.
     */
    public const string DEFAULT_PREFIX = 'neu-csrf';

    /**
     * The prefix to use for the session keys.
     *
     * @var non-empty-string
     */
    private string $prefix;

    /**
     * Creates a new instance of the session storage.
     *
     * @param non-empty-string $prefix The prefix to use for the session keys.
     */
    public function __construct(string $prefix = self::DEFAULT_PREFIX)
    {
        $this->prefix = $prefix;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasToken(RequestInterface $request, string $identifier): bool
    {
        $session = $this->getSession($request);

        return $session->has($this->prefix . ':' . $identifier);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getToken(RequestInterface $request, string $identifier): string
    {
        $session = $this->getSession($request);

        try {
            $value = (string) $session->get($this->prefix . ':' . $identifier);
            if ('' === $value) {
                throw TokenNotFoundException::forIdentifier($identifier);
            }

            return $value;
        } catch (UnavailableItemException $e) {
            throw TokenNotFoundException::forIdentifier($identifier);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setToken(RequestInterface $request, string $identifier, #[SensitiveParameter] string $value): void
    {
        $session = $this->getSession($request);
        $session->set($this->prefix . ':' . $identifier, $value);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function removeToken(RequestInterface $request, string $identifier): void
    {
        $session = $this->getSession($request);
        $session->delete($this->prefix . ':' . $identifier);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function clear(RequestInterface $request): void
    {
        $session = $this->getSession($request);
        /** @var non-empty-string $key */
        foreach ($session->all() as $key => $_) {
            if (Str\starts_with($key, $this->prefix . ':')) {
                $session->delete($key);
            }
        }
    }

    /**
     * Retrieves the session from the request.
     *
     * @param RequestInterface $request The request to retrieve the session from.
     *
     * @throws RuntimeException If the session cannot be retrieved.
     *
     * @return SessionInterface The session.
     */
    private function getSession(RequestInterface $request): SessionInterface
    {
        try {
            return $request->getSession();
        } catch (LogicException $e) {
            throw new RuntimeException('Failed to retrieve session from request.', previous: $e);
        }
    }
}
