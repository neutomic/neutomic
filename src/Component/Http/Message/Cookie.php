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

namespace Neu\Component\Http\Message;

use Override;

final readonly class Cookie implements CookieInterface
{
    public function __construct(
        private string $value,
        private null|int $expires = null,
        private null|int $maxAge = null,
        private null|string $path = null,
        private null|string $domain = null,
        private null|bool $secure = null,
        private null|bool $httpOnly = null,
        private null|CookieSameSite $sameSite = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getExpires(): null|int
    {
        return $this->expires;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMaxAge(): null|int
    {
        return $this->maxAge;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPath(): null|string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDomain(): null|string
    {
        return $this->domain;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSecure(): null|bool
    {
        return $this->secure;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getHttpOnly(): null|bool
    {
        return $this->httpOnly;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSameSite(): null|CookieSameSite
    {
        return $this->sameSite;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withValue(string $value): static
    {
        return new self(
            value: $value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withExpires(null|int $expires): static
    {
        return new self(
            value: $this->value,
            expires: $expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withMaxAge(null|int $maxAge): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withPath(null|string $path): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withDomain(null|string $domain): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withSecure(null|bool $secure): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $secure,
            httpOnly: $this->httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withHttpOnly(null|bool $httpOnly): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $httpOnly,
            sameSite: $this->sameSite,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withSameSite(null|CookieSameSite $sameSite): static
    {
        return new self(
            value: $this->value,
            expires: $this->expires,
            maxAge: $this->maxAge,
            path: $this->path,
            domain: $this->domain,
            secure: $this->secure,
            httpOnly: $this->httpOnly,
            sameSite: $sameSite,
        );
    }
}
