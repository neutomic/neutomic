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

use Neu\Component\Http\Message\Internal\CookieStorage;
use Neu\Component\Http\Message\Internal\HeaderStorage;
use Psl\Dict;

final readonly class Response implements ResponseInterface
{
    use Internal\ExchangeConvenienceMethodsTrait;

    /**
     * The HTTP status code.
     *
     * @var int<100, 599>
     */
    private int $statusCode;

    /**
     * The response cookies.
     *
     * @var CookieStorage<CookieInterface>
     */
    private CookieStorage $cookieStorage;

    /**
     * The response body.
     */
    protected null|BodyInterface $body;

    /**
     * Creates a new response instance.
     *
     * @param int<100, 599> $statusCode
     * @param CookieStorage<CookieInterface> $cookies
     * @param null|BodyInterface $body
     * @param array<non-empty-string, TrailerInterface> $trailers
     */
    private function __construct(ProtocolVersion $protocolVersion, int $statusCode, HeaderStorage $headers, CookieStorage $cookies, null|BodyInterface $body = null, array $trailers = [])
    {
        $this->protocolVersion = $protocolVersion;
        $this->headerStorage = $headers;
        $this->statusCode = $statusCode;
        $this->cookieStorage = $cookies;
        $this->body = $body;
        $this->trailers = $trailers;
    }

    /**
     * Creates a new response instance.
     *
     * @param ProtocolVersion $version
     * @param int<100, 599>|StatusCode $statusCode
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     * @param array<non-empty-string, non-empty-list<CookieInterface>> $cookies
     * @param null|BodyInterface $body
     * @param array<TrailerInterface> $trailers
     */
    public static function create(
        ProtocolVersion $version = ProtocolVersion::Http20,
        int|StatusCode $statusCode = StatusCode::OK,
        array $headers = [],
        array $cookies = [],
        null|BodyInterface $body = null,
        array $trailers = [],
    ): static {
        if ($statusCode instanceof StatusCode) {
            $statusCode = $statusCode->value;
        }

        $trailers = Dict\reindex(
            $trailers,
            static fn (TrailerInterface $trailer): string => $trailer->getField(),
        );

        return new self(
            $version,
            $statusCode,
            HeaderStorage::fromHeaders($headers),
            CookieStorage::fromCookies($cookies),
            $body,
            $trailers,
        );
    }

    /**
     * Creates a new response instance from the given status code.
     *
     * @param int<100, 599>|StatusCode $statusCode
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     */
    public static function fromStatusCode(int|StatusCode $statusCode, array $headers = []): static
    {
        return self::create(statusCode: $statusCode, headers: $headers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withProtocolVersion(ProtocolVersion $version): static
    {
        if ($this->protocolVersion === $version) {
            return clone $this;
        }

        return new self($version, $this->statusCode, $this->headerStorage, $this->cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getBody(): null|BodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withBody(null|BodyInterface $body): static
    {
        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $this->cookieStorage, $body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withStatus(int|StatusCode $code): static
    {
        if ($code instanceof StatusCode) {
            $code = $code->value;
        }

        if ($this->statusCode === $code) {
            return clone $this;
        }

        return new self($this->protocolVersion, $code, $this->headerStorage, $this->cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getCookies(): array
    {
        return $this->cookieStorage->getCookies();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function hasCookie(string $name): bool
    {
        return $this->cookieStorage->hasCookie($name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getCookie(string $name): null|array
    {
        return $this->cookieStorage->getCookie($name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withCookie(string $name, CookieInterface|array $value): static
    {
        $cookieStorage = $this->cookieStorage->withCookie($name, $value);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withAddedCookie(string $name, CookieInterface|array $value): static
    {
        $cookieStorage = $this->cookieStorage->withAddedCookie($name, $value);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function withoutCookie(string $name): static
    {
        $cookieStorage = $this->cookieStorage->withoutCookie($name);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookieStorage, $this->body, $this->trailers);
    }

    #[\Override]
    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self($this->protocolVersion, $this->statusCode, $headerStorage, $this->cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @param array<non-empty-string, TrailerInterface> $trailers
     */
    #[\Override]
    protected function cloneWithTrailers(array $trailers): static
    {
        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $this->cookieStorage, $this->body, $trailers);
    }
}
