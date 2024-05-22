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

use InvalidArgumentException;
use Stringable;

interface UriInterface extends Stringable
{
    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return a null value.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986 Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @return null|non-empty-string
     */
    public function getScheme(): null|string;

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return a null value.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     *  [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     *
     * @return null|non-empty-string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority(): null|string;

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return a null value.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST NOT be added.
     *
     * @return null|non-empty-string The URI user information, in "username[:password]" format.
     */
    public function getUserInformation(): null|string;

    /**
     * Return an instance with the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param non-empty-string $user The username to use for authority.
     * @param non-empty-string|null $password The password associated with $user.
     *
     * @throws InvalidArgumentException for invalid user or password.
     */
    public function withUserInformation(string $user, null|string $password = null): self;

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return a null value.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986 Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @return null|non-empty-string The URI host.
     */
    public function getHost(): null|string;

    /**
     * Return an instance with the specified host.
     *
     * A null host value is equivalent to removing the host.
     *
     * @param non-empty-string|null $host The hostname to use with the new instance; a null value removes the host information.
     *
     * @throws InvalidArgumentException for invalid hostnames.
     */
    public function withHost(null|string $host): self;

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int<0, 65535> The URI port.
     */
    public function getPort(): null|int;

    /**
     * Return an instance with the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param int<0, 65535>|null $port The port to use with the new instance; a null value removes the port information.
     *
     * @throws InvalidArgumentException for invalid ports.
     */
    public function withPort(null|int $port): self;

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be absolute (starting with a slash) or rootless (not starting with a slash).
     * Implementations MUST support both syntax's.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode any characters.
     * To determine what characters to encode, please refer to RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     *
     * @return non-empty-string The URI path.
     */
    public function getPath(): string;

    /**
     * Return an instance with the specified path.
     *
     * The path can either be absolute (starting with a slash) or rootless (not starting with a slash).
     * Implementations MUST support both syntax's.
     *
     * If the path is intended to be domain-relative rather than path-relative, then it must begin with a slash ("/").
     * Paths not starting with a slash ("/") are assumed to be relative to some base path known to the application or consumer.
     *
     * Users must provide a non-empty string for the path.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param non-empty-string $path The URI path.
     *
     * @throws InvalidArgumentException for invalid paths.
     */
    public function withPath(string $path): self;

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return a null value.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     *
     * @return null|non-empty-string The URI query string.
     */
    public function getQuery(): null|string;

    /**
     * Return an instance with the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * A null query string value is equivalent to removing the query string.
     *
     * @param non-empty-string $query The query string to use with the new instance; a null value removes the query string.
     *
     * @throws InvalidArgumentException for invalid query strings.
     */
    public function withQuery(null|string $query): self;

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return a null value.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @return null|non-empty-string The URI fragment.
     */
    public function getFragment(): null|string;

    /**
     * Return an instance with the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * A null fragment value is equivalent to removing the fragment.
     *
     * @param non-empty-string|null $fragment The fragment to use with the new instance; a null value removes the fragment.
     *
     * @throws InvalidArgumentException for invalid fragment value.
     */
    public function withFragment(null|string $fragment): self;

    /**
     * Return an instance with the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * A null scheme is equivalent to removing the scheme.
     *
     * @param non-empty-string|null $scheme The scheme to use with the new instance; a null value removes the scheme.
     *
     * @throws InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme(null|string $scheme): self;

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     *
     * @return non-empty-string The URI as a string.
     */
    public function toString(): string;

    /**
     * An alias for {@see UriInterface::toString()}.
     *
     * @return non-empty-string The URI as a string.
     */
    public function __toString(): string;
}
