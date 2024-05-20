<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

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
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getExpires(): ?int
    {
        return $this->expires;
    }

    /**
     * @inheritDoc
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @inheritDoc
     */
    public function getSecure(): ?bool
    {
        return $this->secure;
    }

    /**
     * @inheritDoc
     */
    public function getHttpOnly(): ?bool
    {
        return $this->httpOnly;
    }

    /**
     * @inheritDoc
     */
    public function getSameSite(): ?CookieSameSite
    {
        return $this->sameSite;
    }

    /**
     * @inheritDoc
     */
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
