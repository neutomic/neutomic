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
use Neu\Component\Http\Message\Exception\LogicException;
use Neu\Component\Http\Session\SessionInterface;

interface RequestInterface extends ExchangeInterface
{
    /**
     * Retrieves the body of the request.
     *
     * @return null|RequestBodyInterface Returns the body of the request or null if the body has not been set.
     */
    public function getBody(): null|RequestBodyInterface;

    /**
     * Retrieves the HTTP method of the request.
     */
    public function getMethod(): Method;

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     */
    public function withMethod(Method $method): static;

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return non-empty-string
     */
    public function getRequestTarget(): string;

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * @param non-empty-string $requestTarget
     *
     * @return static
     */
    public function withRequestTarget(string $requestTarget): static;

    /**
     * Retrieves the URI instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     */
    public function getUri(): UriInterface;

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param bool $preserveHost Preserve the original state of the Host header.
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): static;

    /**
     * Retrieves all cookies associated with the request.
     *
     * This method returns a dictionary where each key is a cookie name and each value is a list of strings representing the
     * cookie's multiple values. The method preserves the case of the cookie names as originally provided.
     *
     * @return array<non-empty-string, non-empty-list<non-empty-string>> A dictionary of all cookies in the request,
     *                                                                   where each key is a cookie name and each value is a list of string values for that cookie.
     */
    public function getCookies(): array;

    /**
     * Checks for the presence of a cookie by name, using a case-insensitive comparison.
     *
     * @param non-empty-string $name The cookie name to check, case-insensitive.
     *
     * @return bool True if the cookie exists, false otherwise.
     */
    public function hasCookie(string $name): bool;

    /**
     * Retrieves all cookies associated with a specified name, if any.
     *
     * This method performs a case-insensitive search and returns all matches. If no matching cookie is found,
     * the method returns null.
     *
     * @param non-empty-string $name The case-insensitive name of the cookie.
     *
     * @return null|non-empty-list<string> A list of string values for the specified name,
     *                                     or null if none found.
     */
    public function getCookie(string $name): null|array;

    /**
     * Returns a new instance of the request with the specified cookies.
     *
     * This method replaces all current cookies with the ones provided in the dictionary. Each cookie name is associated with one or more values.
     *
     * @param array<non-empty-string, non-empty-string|non-empty-list<non-empty-string>> $cookies A dictionary of cookies to set,
     *                                                                                            where each key is a cookie name and each value is a single string or a list of strings.
     *
     * @return static A new instance with the updated cookies.
     */
    public function withCookies(array $cookies): static;

    /**
     * Returns a new instance of the request with the specified cookie replaced.
     *
     * This method replaces the existing values of a cookie with the new value(s) provided. If the cookie does not
     * exist, it is added. Cookie names are treated case-insensitively, but the case of the input name is preserved
     * in the new instance.
     *
     * @param non-empty-string $name The cookie name to replace, case-insensitive.
     * @param non-empty-string|non-empty-list<non-empty-string> $value The new value or values for the cookie.
     *
     * @throws InvalidArgumentException for invalid cookie name.
     *
     * @return static A new instance with the specified cookie updated.
     */
    public function withCookie(string $name, string|array $value): static;

    /**
     * Returns a new instance of the request with the specified cookie value(s) appended.
     *
     * This method appends new value(s) to an existing cookie without removing the previous values. If the cookie
     * does not previously exist, it is created. Cookie names are treated case-insensitively.
     *
     * @param non-empty-string $name The cookie name for which to append values, case-insensitive.
     * @param non-empty-string|non-empty-list<non-empty-string> $value The value or values to append.
     *
     * @throws InvalidArgumentException for invalid cookie name.
     *
     * @return static A new instance with the additional cookie
     */
    public function withAddedCookie(string $name, string|array $value): static;

    /**
     * Returns a new instance of the request without the specified cookie.
     *
     * This method removes all values of a cookie identified by the given name. Cookie names are treated
     * case-insensitively.
     *
     * @param non-empty-string $name The name of the cookie to remove, case-insensitive.
     *
     * @return static A new instance without the specified cookie.
     */
    public function withoutCookie(string $name): static;

    /**
     * Retrieves all query parameters associated with the request.
     *
     * Returns a dictionary where each key is a query parameter name and each value is a list of strings for the corresponding values.
     *
     * @return array<non-empty-string, non-empty-list<string>> A dictionary of all query parameters in the request,
     *                                                         where each key represents the parameter name and each value is a list of string values.
     */
    public function getQueryParameters(): array;

    /**
     * Checks for the presence of a specific query parameter by name.
     *
     * @param non-empty-string $name The query parameter name to check.
     *
     * @return bool True if the query parameter exists, false otherwise.
     */
    public function hasQueryParameter(string $name): bool;

    /**
     * Retrieves all values associated with a specific query parameter by name, if present.
     *
     * This method performs a search to find the query parameter and returns its values as a list of strings.
     * If no such query parameter exists, the method returns null.
     *
     * @param non-empty-string $name The name of the query parameter.
     *
     * @return null|non-empty-list<string> The list of values for the query parameter or null if the parameter does not exist.
     */
    public function getQueryParameter(string $name): null|array;

    /**
     * Returns a new instance of the request with the specified query parameters.
     *
     * This method replaces all current query parameters with the ones provided in the dictionary. Each parameter name is associated with one or more values.
     *
     * The operation does not affect the URI stored by the request.
     *
     * @param array<non-empty-string, string|non-empty-list<string>> $query A dictionary of query parameters to set,
     *                                                                      where each key is a parameter name and each value is a single string or a list of strings.
     *
     * @return static A new instance with the updated query parameters.
     */
    public function withQueryParameters(array $query): static;

    /**
     * Returns a new instance of the request with a specified query parameter replaced or added.
     *
     * This method updates or adds a single query parameter with one or more values.
     *
     * Adding or replacing a query parameter does not change the URI stored by the request.
     *
     * @param non-empty-string $name The query parameter name to replace or add.
     * @param non-empty-string|non-empty-list<string> $value The value or values to set for the query parameter.
     *
     * @return static A new instance with the updated query parameters.
     */
    public function withQueryParameter(string $name, string|array $value): static;

    /**
     * Returns a new instance of the request with additional values appended to a specified query parameter.
     *
     * This method appends new value(s) to an existing query parameter without removing the previous values. If the query parameter
     * does not previously exist, it is created. Parameter names are treated case-insensitively.
     *
     * @param non-empty-string $name The query parameter name for which to append values.
     * @param non-empty-string|non-empty-list<string> $value The value or values to append.
     *
     * @return static A new instance with the additional query parameter values.
     */
    public function withAddedQueryParameter(string $name, string|array $value): static;

    /**
     * Returns a new instance of the request without the specified query parameter.
     *
     * This method removes a query parameter identified by the given name, treating the name case-insensitively.
     *
     * @param non-empty-string $name The name of the query parameter to remove, case-insensitive.
     *
     * @return static A new instance without the specified query parameter.
     */
    public function withoutQueryParameter(string $name): static;

    /**
     * Retrieves all attributes derived from the request.
     *
     * Attributes may include any parameters derived from the request, such as path matches, decrypted cookies,
     * or deserialized body contents, and are specific to the application and request.
     *
     * @return array<non-empty-string, mixed> An associative array of attributes derived from the request.
     */
    public function getAttributes(): array;

    /**
     * Checks if a specific attribute exists by name in the request.
     *
     * @param non-empty-string $name The name of the attribute to check.
     *
     * @return bool True if the attribute exists, false otherwise.
     */
    public function hasAttribute(string $name): bool;

    /**
     * Retrieves a single attribute derived from the request by name.
     *
     * @param non-empty-string $name The name of the attribute to retrieve.
     *
     * @throws LogicException If the attribute is not found.
     *
     * @return mixed The value of the attribute.
     */
    public function getAttribute(string $name): mixed;

    /**
     * Returns a new instance of the request with the specified attributes.
     *
     * This method replaces all current attributes with the ones provided in the dictionary.
     *
     * @param array<non-empty-string, mixed> $attributes A dictionary of attributes to set, where each key is an attribute name and each value is the attribute value.
     *
     * @return static A new instance with the updated attributes.
     */
    public function withAttributes(array $attributes): static;

    /**
     * Returns a new instance of the request with the specified attributes added.
     *
     * This method adds the provided attributes to the existing set of attributes.
     *
     * @param array<non-empty-string, mixed> $attributes A dictionary of attributes to add, where each key is an attribute name and each value is the attribute value.
     *
     * @return static A new instance with the added attributes.
     */
    public function withAddedAttributes(array $attributes): static;

    /**
     * Returns a new instance of the request with a specified attribute set.
     *
     * This method updates or adds a single attribute.
     *
     * @param non-empty-string $name The name of the attribute to set.
     * @param mixed $value The value of the attribute to set.
     *
     * @return static A new instance with the updated attribute.
     */
    public function withAttribute(string $name, mixed $value): static;

    /**
     * Returns a new instance of the request without a specified attribute.
     *
     * @param non-empty-string $name The name of the attribute to remove.
     *
     * @return static A new instance without the specified attribute.
     */
    public function withoutAttribute(string $name): static;

    /**
     * Checks if the request has a session associated with it.
     *
     * @return bool True if a session is associated with the request, false otherwise.
     */
    public function hasSession(): bool;

    /**
     * Retrieves the session associated with the request.
     *
     * @throws LogicException If no session is associated with the request.
     *
     * @return SessionInterface The session object associated with the request.
     */
    public function getSession(): SessionInterface;

    /**
     * Returns a new instance of the request with the specified session.
     *
     * If a null value is provided, the session is removed from the request.
     *
     * @param ?SessionInterface $session The new session object to associate, or null to remove the session.
     *
     * @return static A new instance with the specified session.
     */
    public function withSession(null|SessionInterface $session): static;
}
