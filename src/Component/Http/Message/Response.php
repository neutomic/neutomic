<?php

declare(strict_types=1);

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
     * @param array<string, TrailerInterface> $trailers
     */
    private function __construct(ProtocolVersion $protocolVersion, int $statusCode, HeaderStorage $headers, CookieStorage $cookies, ?BodyInterface $body = null, array $trailers = [])
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
     * @param array<string, list<string>> $headers
     * @param array<string, list<string>> $cookies
     * @param null|BodyInterface $body
     * @param iterable<TrailerInterface> $trailers
     */
    public static function create(
        ProtocolVersion $version = ProtocolVersion::Http20,
        int|StatusCode $statusCode = StatusCode::OK,
        array $headers = [],
        array $cookies = [],
        array $pushes = [],
        ?BodyInterface $body = null,
        array $trailers = [],
    ): static {
        if ($statusCode instanceof StatusCode) {
            $statusCode = $statusCode->value;
        }

        $trailers = Dict\reindex(
            $trailers,
            static fn(TrailerInterface $trailer): string => $trailer->getField(),
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
     * @param array<string, list<string>> $headers
     */
    public static function fromStatusCode(int|StatusCode $statusCode, array $headers = []): static
    {
        return self::create(statusCode: $statusCode, headers: $headers);
    }

    /**
     * @inheritDoc
     */
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
    public function getBody(): ?BodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(?BodyInterface $body): static
    {
        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $this->cookieStorage, $body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
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
    public function getCookies(): array
    {
        return $this->cookieStorage->getCookies();
    }

    /**
     * @inheritDoc
     */
    public function hasCookie(string $name): bool
    {
        return $this->cookieStorage->hasCookie($name);
    }

    /**
     * @inheritDoc
     */
    public function getCookie(string $name): ?array
    {
        return $this->cookieStorage->getCookie($name);
    }

    /**
     * @inheritDoc
     */
    public function withCookie(string $name, CookieInterface|array $value): static
    {
        $cookieStorage = $this->cookieStorage->withCookie($name, $value);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    public function withAddedCookie(string $name, CookieInterface|array $value): static
    {
        $cookieStorage = $this->cookieStorage->withAddedCookie($name, $value);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @inheritDoc
     */
    public function withoutCookie(string $name): static
    {
        $cookies = $this->cookieStorage;
        unset($cookies[$name]);

        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $cookies, $this->body, $this->trailers);
    }

    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self($this->protocolVersion, $this->statusCode, $headerStorage, $this->cookieStorage, $this->body, $this->trailers);
    }

    /**
     * @param array<string, TrailerInterface> $trailers
     */
    protected function cloneWithTrailers(array $trailers): static
    {
        return new self($this->protocolVersion, $this->statusCode, $this->headerStorage, $this->cookieStorage, $this->body, $trailers);
    }
}
