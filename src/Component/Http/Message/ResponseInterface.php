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

interface ResponseInterface extends ExchangeInterface
{
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int<100, 599>
     */
    public function getStatusCode(): int;

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int<100, 599>|StatusCode $code
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    public function withStatus(int|StatusCode $code): static;

    /**
     * Retrieves all cookies associated with the response.
     *
     * This method returns a dictionary where each key is a cookie name and each value is a list of {@see CookieInterface} representing the
     * cookie's multiple values. The method preserves the case of the cookie names as originally provided.
     *
     * @return array<non-empty-string, non-empty-list<CookieInterface>> A dictionary of all cookies in the response,
     *                                                                  where each key is a cookie name and each value is a list of {@see CookieInterface} values for that cookie.
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
     * @return null|list<CookieInterface> A list of {@see CookieInterface} values for the specified name,
     *                                    or null if none found.
     */
    public function getCookie(string $name): null|array;

    /**
     * Returns a new instance of the response with the specified cookie replaced.
     *
     * This method replaces the existing values of a cookie with the new value(s) provided. If the cookie does not
     * exist, it is added. Cookie names are treated case-insensitively, but the case of the input name is preserved
     * in the new instance.
     *
     * @param non-empty-string $name The cookie name to replace, case-insensitive.
     * @param CookieInterface|list<CookieInterface> $value The new value or values for the cookie.
     *
     * @throws InvalidArgumentException for invalid cookie name.
     *
     * @return static A new instance with the specified cookie updated.
     */
    public function withCookie(string $name, CookieInterface|array $value): static;

    /**
     * Returns a new instance of the response with the specified cookie value(s) appended.
     *
     * This method appends new value(s) to an existing cookie without removing the previous values. If the cookie
     * does not previously exist, it is created. Cookie names are treated case-insensitively.
     *
     * @param non-empty-string $name The cookie name for which to append values, case-insensitive.
     * @param CookieInterface|list<CookieInterface> $value The value or values to append.
     *
     * @throws InvalidArgumentException for invalid cookie name.
     *
     * @return static A new instance with the additional cookie
     */
    public function withAddedCookie(string $name, CookieInterface|array $value): static;

    /**
     * Returns a new instance of the response without the specified cookie.
     *
     * This method removes all values of a cookie identified by the given name. Cookie names are treated
     * case-insensitively.
     *
     * @param non-empty-string $name The name of the cookie to remove, case-insensitive.
     *
     * @return static A new instance without the specified cookie.
     */
    public function withoutCookie(string $name): static;
}
