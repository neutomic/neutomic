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

namespace Neu\Component\Csrf;

use Neu\Component\Csrf\Generator\CsrfTokenGeneratorInterface;
use Neu\Component\Csrf\Generator\UrlSafeCsrfTokenGenerator;
use Neu\Component\Csrf\Storage\CsrfTokenStorageInterface;
use Neu\Component\Csrf\Storage\SessionCsrfTokenStorage;
use Neu\Component\Http\Message\RequestInterface;
use Psl\Hash;
use SensitiveParameter;

/**
 * A generic CSRF token manager.
 */
final readonly class CsrfTokenManager implements CsrfTokenManagerInterface
{
    /**
     * The CSRF token generator.
     */
    private CsrfTokenGeneratorInterface $generator;

    /**
     * The CSRF token storage.
     */
    private CsrfTokenStorageInterface $storage;

    /**
     * Creates a new {@see CsrfTokenManager} instance.
     *
     * @param null|CsrfTokenGeneratorInterface $generator An optional CSRF token generator, defaults to {@see UrlSafeCsrfTokenGenerator}.
     * @param null|CsrfTokenStorageInterface $storage An optional CSRF token storage, defaults to {@see SessionCsrfTokenStorage}.
     */
    public function __construct(null|CsrfTokenGeneratorInterface $generator = null, null|CsrfTokenStorageInterface $storage = null)
    {
        $this->generator = $generator ?? new UrlSafeCsrfTokenGenerator();
        $this->storage = $storage ?? new SessionCsrfTokenStorage();
    }

    /**
     * @inheritDoc
     */
    public function getToken(RequestInterface $request, string $identifier): string
    {
        return $this->storage->getToken($request, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function getOrCreateToken(RequestInterface $request, string $identifier): string
    {
        if ($this->storage->hasToken($request, $identifier)) {
            return $this->storage->getToken($request, $identifier);
        }

        $token = $this->generator->generate();

        $this->storage->setToken($request, $identifier, $token);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function rotateToken(RequestInterface $request, string $identifier): string
    {
        $token = $this->generator->generate();

        $this->storage->setToken($request, $identifier, $token);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function removeToken(RequestInterface $request, string $identifier): void
    {
        $this->storage->removeToken($request, $identifier);
    }

    /**
     * @inheritDoc
     */
    public function validateToken(RequestInterface $request, string $identifier, #[SensitiveParameter] string $value): bool
    {
        if (!$this->storage->hasToken($request, $identifier)) {
            return false;
        }

        $token = $this->storage->getToken($request, $identifier);

        return Hash\equals($token, $value);
    }
}
