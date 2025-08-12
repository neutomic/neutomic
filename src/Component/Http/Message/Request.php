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

use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Exception\LogicException;
use Neu\Component\Http\Message\Internal\CookieStorage;
use Neu\Component\Http\Message\Internal\HeaderStorage;
use Neu\Component\Http\Session\SessionInterface;
use Override;

use function array_merge;

final readonly class Request implements RequestInterface
{
    use Internal\ExchangeConvenienceMethodsTrait;

    /**
     * The method of the request.
     */
    private Method $method;

    /**
     * The request target of the request.
     *
     * @var non-empty-string
     */
    private string $requestTarget;

    /**
     * The URI of the request.
     */
    private UriInterface $uri;

    /**
     * The cookies of the request.
     *
     * @var CookieStorage<non-empty-string>
     */
    private CookieStorage $cookies;

    /**
     * The query parameters of the request.
     *
     * @var array<non-empty-string, non-empty-list<string>>
     */
    private array $queryParameters;

    /**
     * The session of the request.
     */
    private null|SessionInterface $session;

    /**
     * The attributes of the request.
     *
     * @var array<non-empty-string, mixed>
     */
    private array $attributes;

    /**
     * The request body.
     */
    protected null|RequestBodyInterface $body;

    /**
     * Creates a new request instance.
     *
     * @param array<non-empty-string, non-empty-list<string>> $queryParameters
     * @param array<non-empty-string, mixed> $attributes
     * @param array<non-empty-string, TrailerInterface> $trailers
     * @param CookieStorage<non-empty-string> $cookies
     */
    private function __construct(
        ProtocolVersion $protocolVersion,
        Method $method,
        null|string $requestTarget,
        UriInterface $uri,
        HeaderStorage $headerStorage,
        CookieStorage $cookies,
        array $queryParameters = [],
        null|SessionInterface $session = null,
        array $attributes = [],
        null|RequestBodyInterface $body = null,
        array $trailers = []
    ) {
        if ($requestTarget === null) {
            $requestTarget = $uri->getPath();
            $query = $uri->getQuery();
            if ($query !== null) {
                $requestTarget .= '?' . $query;
            }

            $fragment = $uri->getFragment();
            if ($fragment !== null) {
                $requestTarget .= '#' . $fragment;
            }

            $requestTarget = $requestTarget ?: '/';
        } else {
            $requestTarget = '/';
        }

        $this->protocolVersion = $protocolVersion;
        $this->method = $method;
        $this->requestTarget = $requestTarget;
        $this->uri = $uri;
        $this->headerStorage = $headerStorage;
        $this->cookies = $cookies;
        $this->queryParameters = $queryParameters;
        $this->session = $session;
        $this->attributes = $attributes;
        $this->body = $body;
        $this->trailers = $trailers;
    }

    /**
     * Creates a new request instance using the given parameters.
     *
     * @param UriInterface|non-empty-string $uri
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $cookies
     *
     * @throws InvalidArgumentException If $uri is not a valid URI.
     */
    public static function create(Method $method, UriInterface|string $uri, array $headers = [], array $cookies = []): static
    {
        if (!$uri instanceof UriInterface) {
            $uri = Uri::fromUrl($uri);
        }

        $headerStorage = HeaderStorage::fromHeaders($headers);
        $cookieStorage = CookieStorage::fromCookies($cookies);

        return new self(ProtocolVersion::Http11, $method, null, $uri, $headerStorage, $cookieStorage);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withProtocolVersion(ProtocolVersion $version): static
    {
        if ($this->protocolVersion === $version) {
            return clone $this;
        }

        return new self(
            $version,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBody(): null|RequestBodyInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withBody(null|BodyInterface $body): static
    {
        if (null !== $body && !$body instanceof RequestBodyInterface) {
            /** @psalm-suppress MissingThrowsDocblock */
            $body = RequestBody::fromIterable($body->getIterator());
        }

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withMethod(Method $method): static
    {
        if ($this->method === $method) {
            return clone $this;
        }

        return new self(
            $this->protocolVersion,
            $method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withRequestTarget(string $requestTarget): static
    {
        if ($this->requestTarget === $requestTarget) {
            return clone $this;
        }

        return new self(
            $this->protocolVersion,
            $this->method,
            $requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        if ($this->uri === $uri) {
            return clone $this;
        }

        $request = new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );

        if ($preserveHost && $this->hasHeader('Host')) {
            return $request;
        }

        $host = $uri->getHost();
        if (null === $host) {
            return $request;
        }

        $port = $uri->getPort();
        if ($port !== null) {
            $host .= ':' . ((string) $port);
        }

        /** @psalm-suppress MissingThrowsDocblock - header is valid at this point */
        return $request->withHeader('Host', $host);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getCookies(): array
    {
        return $this->cookies->getCookies();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasCookie(string $name): bool
    {
        return $this->cookies->hasCookie($name);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getCookie(string $name): null|array
    {
        return $this->cookies->getCookie($name);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withCookies(array $cookies): static
    {
        $cookieStorage = CookieStorage::fromCookies($cookies);

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $cookieStorage,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withCookie(string $name, array|string $value): static
    {
        $cookies = $this->cookies->withCookie($name, $value);

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withAddedCookie(string $name, array|string $value): static
    {
        $cookies = $this->cookies->withAddedCookie($name, $value);

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withoutCookie(string $name): static
    {
        $cookies = $this->cookies->withoutCookie($name);

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasQueryParameter(string $name): bool
    {
        return isset($this->queryParameters[$name]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueryParameter(string $name): null|array
    {
        return $this->queryParameters[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withQueryParameters(array $query): static
    {
        $query_parameters = [];
        foreach ($query as $name => $value) {
            $query_parameters[$name] = (array) $value;
        }

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $query_parameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withQueryParameter(string $name, string|array $value): static
    {
        $query = $this->queryParameters;
        $query[$name] = (array) $value;

        return $this->withQueryParameters($query);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withAddedQueryParameter(string $name, string|array $value): static
    {
        $query = $this->queryParameters;
        $query[$name] = array_merge($query[$name] ?? [], (array) $value);

        return $this->withQueryParameters($query);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withoutQueryParameter(string $name): static
    {
        $query = $this->queryParameters;
        unset($query[$name]);

        return $this->withQueryParameters($query);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withAttributes(array $attributes): static
    {
        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withAddedAttributes(array $attributes): static
    {
        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            array_merge($this->attributes, $attributes),
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withAttribute(string $name, mixed $value): static
    {
        $attributes = $this->attributes;
        /** @psalm-suppress MixedAssignment */
        $attributes[$name] = $value;

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withoutAttribute(string $name): static
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);

        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $attributes,
            $this->body
        );
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasSession(): bool
    {
        return $this->session !== null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getSession(): SessionInterface
    {
        if ($this->session === null) {
            throw new LogicException('The request does not have a session.');
        }

        return $this->session;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function withSession(null|SessionInterface $session): static
    {
        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $session,
            $this->attributes,
            $this->body
        );
    }

    #[Override]
    protected function cloneWithHeaderStorage(HeaderStorage $headerStorage): static
    {
        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body
        );
    }

    #[Override]
    protected function cloneWithTrailers(array $trailers): static
    {
        return new self(
            $this->protocolVersion,
            $this->method,
            $this->requestTarget,
            $this->uri,
            $this->headerStorage,
            $this->cookies,
            $this->queryParameters,
            $this->session,
            $this->attributes,
            $this->body,
            $trailers
        );
    }
}
