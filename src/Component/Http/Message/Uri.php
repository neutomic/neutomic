<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

use Neu\Component\Http\Message\Exception\InvalidArgumentException;

use function ltrim;
use function parse_url;
use function preg_replace_callback;
use function rawurlencode;
use function strtolower;

final readonly class Uri implements UriInterface
{
    private const array SCHEMES = ['http' => 80, 'https' => 443];

    /**
     * The URI scheme.
     *
     * @var null|non-empty-string
     */
    private ?string $scheme;


    /**
     * The URI user information.
     *
     * @var null|non-empty-string
     */
    private ?string $userInformation ;

    /**
     * The URI host.
     *
     * @var null|non-empty-string
     */
    private ?string $host ;

    /**
     * The URI port.
     *
     * @var null|int<0, 65535>
     */
    private ?int $port ;

    /**
     * The URI path.
     *
     * @var string
     */
    private string $path ;

    /**
     * The URI query.
     *
     * @var null|non-empty-string
     */
    private ?string $query;

    /**
     * The URI fragment.
     *
     * @var null|non-empty-string
     */
    private ?string $fragment;

    /**
     * @param null|non-empty-string $scheme
     * @param null|non-empty-string $userInformation
     * @param null|non-empty-string $host
     * @param null|int<0, 65535> $port
     * @param non-empty-string $path
     * @param null|non-empty-string $query
     * @param null|non-empty-string $fragment
     */
    private function __construct(
        null|string $scheme = null,
        null|string $userInformation = null,
        null|string $host = null,
        null|int $port = null,
        string $path = '',
        null|string $query = null,
        null|string $fragment = null
    ) {
        $this->scheme = $scheme;
        $this->userInformation = $userInformation;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * Create a new URI from parts.
     *
     * @param null|non-empty-string $scheme
     * @param null|non-empty-string $user
     * @param null|non-empty-string $password
     * @param null|non-empty-string $host
     * @param null|int<0, 65535> $port
     * @param non-empty-string $path
     * @param null|non-empty-string $query
     * @param null|non-empty-string $fragment
     *
     * @return Uri The URI generated from the parts.
     */
    public static function fromParts(
        ?string $scheme = null,
        ?string $user = null,
        ?string $password = null,
        ?string $host = null,
        ?int $port = null,
        string $path = '',
        ?string $query = null,
        ?string $fragment = null
    ): self {
        if (null !== $scheme) {
            $scheme = strtolower($scheme);
        }

        if (null !== $user) {
            $userInformation = self::filterUserInformationPart($user);
            if (null !== $password) {
                $userInformation .= ':' . self::filterUserInformationPart($password);
            }
        } else {
            $userInformation = null;
        }

        if (null !== $host) {
            $host = strtolower($host);
        }

        $port = self::normalizePortPart($scheme, $port);
        $path = self::filterPathPart($path);
        $path = self::normalizePathPart($host, $path);

        if (null !== $query) {
            $query = self::filterQueryOrFragmentPart($query);
        }

        if (null !== $fragment) {
            $fragment = self::filterQueryOrFragmentPart($fragment);
        }

        return new self($scheme, $userInformation, $host, $port, $path, $query, $fragment);
    }

    /**
     * Create a new URI from a string.
     *
     * @param non-empty-string $url
     *
     * @throws InvalidArgumentException If the URL is invalid.
     *
     * @return Uri The URI generated from the string.
     */
    public static function fromUrl(string $url): self
    {
        $parsed_url = parse_url($url);
        if ($parsed_url === false) {
            throw new InvalidArgumentException('The given URL "' . $url . '" is invalid.');
        }

        return self::fromParts(
            $parsed_url['scheme'] ?? null,
            $parsed_url['user'] ?? null,
            $parsed_url['pass'] ?? null,
            $parsed_url['host'] ?? null,
            $parsed_url['port'] ?? null,
            $parsed_url['path'] ?? '',
            $parsed_url['query'] ?? null,
            $parsed_url['fragment'] ?? null
        );
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(string $scheme): self
    {
        $scheme = strtolower($scheme);
        if ($scheme === $this->scheme) {
            return clone $this;
        }

        return new self($scheme, $this->userInformation, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): string
    {
        if (null === $this->host) {
            return '';
        }

        $authority = $this->host;
        if (null !== $this->userInformation) {
            $authority = $this->userInformation . '@' . $authority;
        }

        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInformation(): ?string
    {
        return $this->userInformation;
    }

    /**
     * @inheritDoc
     */
    public function withUserInformation(string $user, null|string $password = null): self
    {
        $info = self::filterUserInformationPart($user);

        if (null !== $password) {
            $info .= ':' . self::filterUserInformationPart($password);
        }

        if ($this->userInformation === $info) {
            return clone $this;
        }

        return new self($this->scheme, $info, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function withHost(string $host): self
    {
        $host = strtolower($host);
        if ($this->host === $host) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $host, $this->port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port): self
    {
        $port = self::normalizePortPart($this->scheme, $port);
        if ($this->port === $port) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $this->host, $port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function withPath(string $path): self
    {
        $path = self::filterPathPart($path);
        $path = self::normalizePathPart($this->host, $path);
        if ($this->path === $path) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function withQuery(string $query): self
    {
        $query = self::filterQueryOrFragmentPart($query);
        if ($this->query === $query) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $this->path, $query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * @return static
     */
    public function withFragment($fragment): self
    {
        $fragment = self::filterQueryOrFragmentPart($fragment);
        if ($this->fragment === $fragment) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $this->path, $this->query, $fragment);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $uri = '';
        if ('' !== $this->scheme) {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ('' !== $authority) {
            $uri .= '//' . $authority;
        }

        if ('' !== $this->path) {
            $uri .= $this->path;
        }

        if (null !== $this->query) {
            $uri .= '?' . $this->query;
        }

        if (null !== $this->fragment) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Normalize the given port.
     */
    private static function normalizePortPart(?string $scheme, ?int $port): ?int
    {
        if (null === $port || (null !== $scheme && (self::SCHEMES[$scheme] ?? null) === $port)) {
            return null;
        }

        return $port;
    }

    /**
     * Normalize the given path.
     */
    private static function normalizePathPart(?string $host, string $path): string
    {
        if ('' !== $path && '/' !== $path[0]) {
            if (null !== $host) {
                $path = '/' . $path;
            }
        } elseif (isset($path[1]) && '/' === $path[1]) {
            $path = '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * Filter the given user information component of a URI.
     */
    private static function filterUserInformationPart(string $userInformation): string
    {
        return preg_replace_callback(
            '/[:\/\?#\[\]@!\$&\'\(\)\*\+,;=]++/',
            static fn (array $match): string => rawurlencode($match[0]),
            $userInformation,
        );
    }

    /**
     * Filter the given path part of a URI.
     */
    private static function filterPathPart(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            static fn (array $match): string => rawurlencode($match[0]),
            $path,
        );
    }

    /**
     * Filter the given query or fragment part of a URI.
     */
    private static function filterQueryOrFragmentPart(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            static fn (array $match): string => rawurlencode($match[0]),
            $str,
        );
    }
}
