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
    private null|string $scheme;


    /**
     * The URI user information.
     *
     * @var null|non-empty-string
     */
    private null|string $userInformation ;

    /**
     * The URI host.
     *
     * @var null|non-empty-string
     */
    private null|string $host ;

    /**
     * The URI port.
     *
     * @var null|int<0, 65535>
     */
    private null|int $port ;

    /**
     * The URI path.
     *
     * @var non-empty-string
     */
    private string $path;

    /**
     * The URI query.
     *
     * @var null|non-empty-string
     */
    private null|string $query;

    /**
     * The URI fragment.
     *
     * @var null|non-empty-string
     */
    private null|string $fragment;

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
        string $path = '/',
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
     * @throws InvalidArgumentException If the parts are invalid.
     *
     * @return Uri The URI generated from the parts.
     */
    public static function fromParts(
        null|string $scheme = null,
        null|string $user = null,
        null|string $password = null,
        null|string $host = null,
        null|int $port = null,
        string $path = '',
        null|string $query = null,
        null|string $fragment = null
    ): self {
        if ('' === $scheme) {
            $scheme = null;
        }

        if ('' === $user) {
            $user = null;
        }

        if ('' === $password) {
            $password = null;
        }

        if ('' === $host) {
            $host = null;
        }

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
        $path = self::normalizePathPart($path);

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
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false) {
            throw new InvalidArgumentException('The given URL "' . $url . '" is invalid.');
        }

        return self::fromParts(
            $parsedUrl['scheme'] ?? null,
            $parsedUrl['user'] ?? null,
            $parsedUrl['pass'] ?? null,
            $parsedUrl['host'] ?? null,
            $parsedUrl['port'] ?? null,
            $parsedUrl['path'] ?? '',
            $parsedUrl['query'] ?? null,
            $parsedUrl['fragment'] ?? null
        );
    }

    /**
     * @inheritDoc
     */
    public function getScheme(): null|string
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function withScheme(null|string $scheme): self
    {
        if (null !== $scheme) {
            $scheme = strtolower($scheme);
            if ($scheme === $this->scheme) {
                return clone $this;
            }
        }

        return new self($scheme, $this->userInformation, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getAuthority(): null|string
    {
        if (null === $this->host) {
            return null;
        }

        $authority = $this->host;
        if (null !== $this->userInformation) {
            $authority = $this->userInformation . '@' . $authority;
        }

        if (null !== $this->port) {
            $authority .= ':' . ((string) $this->port);
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInformation(): null|string
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
    public function getHost(): null|string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function withHost(null|string $host): self
    {
        if (null !== $host) {
            $host = strtolower($host);
            if ($this->host === $host) {
                return clone $this;
            }
        }

        return new self($this->scheme, $this->userInformation, $host, $this->port, $this->path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getPort(): null|int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port): self
    {
        if (null !== $port) {
            $port = self::normalizePortPart($this->scheme, $port);
            if ($this->port === $port) {
                return clone $this;
            }
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
        $path = self::normalizePathPart($path);
        if ($this->path === $path) {
            return clone $this;
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $path, $this->query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): null|string
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function withQuery(null|string $query): self
    {
        if (null !== $query) {
            $query = self::filterQueryOrFragmentPart($query);
            if ($this->query === $query) {
                return clone $this;
            }
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $this->path, $query, $this->fragment);
    }

    /**
     * @inheritDoc
     */
    public function getFragment(): null|string
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withFragment(null|string $fragment): self
    {
        if (null !== $fragment) {
            $fragment = self::filterQueryOrFragmentPart($fragment);
            if ($this->fragment === $fragment) {
                return clone $this;
            }
        }

        return new self($this->scheme, $this->userInformation, $this->host, $this->port, $this->path, $this->query, $fragment);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $uri = '';
        if (null !== $this->scheme) {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if (null !== $authority) {
            $uri .= '//' . $authority;
        }

        $uri .= $this->path;

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
     *
     * @throws InvalidArgumentException If the port is invalid.
     *
     * @return null|int<0, 65535> The normalized port.
     */
    private static function normalizePortPart(null|string $scheme, null|int $port): null|int
    {
        if (null === $port || (null !== $scheme && (self::SCHEMES[$scheme] ?? null) === $port)) {
            return null;
        }

        if ($port < 0 || $port > 65535) {
            throw new InvalidArgumentException('The given port "' . ((string) $port) . '" is invalid.');
        }

        return $port;
    }

    /**
     * Normalize the given path.
     *
     * @return non-empty-string
     */
    private static function normalizePathPart(string $path): string
    {
        if ('' === $path) {
            return '/';
        }

        if ('/' !== $path[0]) {
            $path = '/' . $path;
        }

        if (isset($path[1]) && '/' === $path[1]) {
            $path = '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * Filter the given user information component of a URI.
     *
     * @throws InvalidArgumentException If the user information is invalid.
     *
     * @return non-empty-string The filtered user information.
     */
    private static function filterUserInformationPart(string $userInformation): string
    {
        $filteredUserInformation = preg_replace_callback(
            '/[:\/\?#\[\]@!\$&\'\(\)\*\+,;=]++/',
            static fn (array $match): string => rawurlencode($match[0]),
            $userInformation,
        );

        if (null === $filteredUserInformation || '' === $filteredUserInformation) {
            throw new InvalidArgumentException('The given user information "' . $userInformation . '" is invalid.');
        }

        return $filteredUserInformation;
    }

    /**
     * Filter the given path part of a URI.
     *
     * @throws InvalidArgumentException If the path is invalid.
     *
     * @return string The filtered path part.
     */
    private static function filterPathPart(string $path): string
    {
        $filteredPath = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            static fn (array $match): string => rawurlencode($match[0]),
            $path,
        );

        if (null === $filteredPath) {
            throw new InvalidArgumentException('The given path "' . $path . '" is invalid.');
        }

        return $filteredPath;
    }

    /**
     * Filter the given query or fragment part of a URI.
     *
     * @throws InvalidArgumentException If the query or fragment part is invalid.
     *
     * @return non-empty-string The filtered query or fragment part.
     */
    private static function filterQueryOrFragmentPart(string $str): string
    {
        $filteredQueryOrFragmentPart = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            static fn (array $match): string => rawurlencode($match[0]),
            $str,
        );

        if (null === $filteredQueryOrFragmentPart || '' === $filteredQueryOrFragmentPart) {
            throw new InvalidArgumentException('The given query or fragment part "' . $str . '" is invalid.');
        }

        return $filteredQueryOrFragmentPart;
    }
}
