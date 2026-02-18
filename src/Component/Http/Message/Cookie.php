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
        private ?int $expires = null,
        private ?int $maxAge = null,
        private ?string $path = null,
        private ?string $domain = null,
        private ?bool $secure = null,
        private ?bool $httpOnly = null,
        private ?CookieSameSite $sameSite = null,
    ) {}

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
    public function getExpires(): ?int
    {
        return $this->expires;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSecure(): ?bool
    {
        return $this->secure;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getHttpOnly(): ?bool
    {
        return $this->httpOnly;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSameSite(): ?CookieSameSite
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
    public function withExpires(?int $expires): static
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
    public function withMaxAge(?int $maxAge): static
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
    public function withPath(?string $path): static
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
    public function withDomain(?string $domain): static
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
    public function withSecure(?bool $secure): static
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
    public function withHttpOnly(?bool $httpOnly): static
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
    public function withSameSite(?CookieSameSite $sameSite): static
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
