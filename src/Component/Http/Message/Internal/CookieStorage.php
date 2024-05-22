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

namespace Neu\Component\Http\Message\Internal;

use Neu\Component\Http\Message\CookieInterface;
use Neu\Component\Http\Message\Exception\InvalidArgumentException;

use function array_merge;
use function is_array;
use function preg_match;
use function strtolower;

/**
 * @template T of non-empty-string|CookieInterface
 *
 * Internal storage for cookies.
 *
 * @internal
 *
 * @psalm-suppress DocblockTypeContradiction
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class CookieStorage
{
    /**
     * The cookies.
     *
     * @var array<non-empty-string, non-empty-list<T>>
     */
    private array $cookies;

    /**
     * The cookie names, normalized to lowercase.
     *
     * @var array<non-empty-lowercase-string, non-empty-string>
     */
    private array $cookieNames;

    /**
     * @param array<non-empty-string, non-empty-list<T>> $cookies
     * @param array<non-empty-lowercase-string, non-empty-string> $cookieNames
     */
    private function __construct(array $cookies, array $cookieNames)
    {
        $this->cookies = $cookies;
        $this->cookieNames = $cookieNames;
    }

    /**
     * Create a new storage from an array of cookies.
     *
     * @template P of non-empty-string|CookieInterface
     *
     * @param array<non-empty-string, P|list<P>> $cookies
     *
     * @return self<P>
     */
    public static function fromCookies(array $cookies): self
    {
        /** @var array<non-empty-lowercase-string, non-empty-string> $cookieNames */
        $cookieNames = [];
        /** @var array<non-empty-string, non-empty-list<P>> $validCookies */
        $validCookies = [];

        foreach ($cookies as $cookie => $value) {
            if ($value === []) {
                continue;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            self::assertCookieNameIsValid($cookie);

            $cookieNames[strtolower($cookie)] = $cookie;
            $validCookies[$cookie] = $value;
        }

        return new self($validCookies, $cookieNames);
    }

    /**
     * Get all cookies.
     *
     * @return array<non-empty-string, non-empty-list<T>>
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Check if a cookie exists.
     *
     * @param non-empty-string $name The cookie name.
     */
    public function hasCookie(string $name): bool
    {
        return isset($this->cookieNames[strtolower($name)]);
    }

    /**
     * Get a cookie by name.
     *
     * @param non-empty-string $name The cookie name.
     *
     * @return null|non-empty-list<T> The cookie value, or null if the cookie does not exist.
     */
    public function getCookie(string $name): null|array
    {
        $normalized = strtolower($name);
        if (!isset($this->cookieNames[$normalized])) {
            return null;
        }

        $name = $this->cookieNames[$normalized];

        return $this->cookies[$name];
    }

    /**
     * Add a cookie to the storage.
     *
     * @param non-empty-string $name The cookie name.
     * @param T|list<T> $value The cookie value(s).
     *
     * @return self<T> The updated storage.
     */
    public function withCookie(string $name, mixed $value): self
    {
        self::assertCookieNameIsValid($name);

        $normalized = strtolower($name);
        $cookies = $this->cookies;
        $cookieNames = $this->cookieNames;
        if ($this->hasCookie($name)) {
            unset($cookies[$this->cookieNames[$normalized]]);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $cookieNames[$normalized] = $name;
        $cookies[$name] = $value;

        return new self($cookies, $cookieNames);
    }

    /**
     * Add a value to an existing cookie.
     *
     * @param non-empty-string $name The cookie name.
     * @param T|list<T> $value The value(s) to add.
     *
     * @return self<T> The updated storage.
     */
    public function withAddedCookie(string $name, mixed $value): self
    {
        self::assertCookieNameIsValid($name);

        if (!$this->hasCookie($name)) {
            return $this->withCookie($name, $value);
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $name = $this->cookieNames[strtolower($name)];
        $cookies = $this->cookies;
        $cookies[$name] = array_merge($this->cookies[$name], $value);

        return new self($cookies, $this->cookieNames);
    }

    /**
     * Remove a cookie from the storage.
     *
     * @param non-empty-string $name The cookie name.
     *
     * @return self<T> The updated storage.
     */
    public function withoutCookie(string $name): self
    {
        if (!$this->hasCookie($name)) {
            return clone $this;
        }

        $normalized = strtolower($name);
        $cookie   = $this->cookieNames[$normalized];

        $cookies = $this->cookies;
        $cookieNames = $this->cookieNames;
        unset($cookies[$cookie], $cookieNames[$normalized]);

        return new self($cookies, $cookieNames);
    }

    /**
     * Assert whether a cookie name is valid.
     *
     * @throws InvalidArgumentException If the cookie name is not valid.
     */
    public static function assertCookieNameIsValid(string $name): void
    {
        // Check if the name contains any control characters, spaces, or tabs
        if (preg_match('/[\x00-\x1F\x7F]/', $name)) {
            throw new InvalidArgumentException('"' . $name . '" is not a valid cookie name');
        }

        // Check if the name contains any invalid characters
        if (preg_match('/[()<>@,;:\"\/\[\]?={} ]/', $name)) {
            throw new InvalidArgumentException('"' . $name . '" is not a valid cookie name');
        }
    }
}
