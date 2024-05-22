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

namespace Neu\Component\Http\Session\Configuration;

use Neu\Component\Http\Message\CookieSameSite;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\SessionInterface;

/**
 * Represents the configuration for session cookies.
 */
final readonly class CookieConfiguration
{
    /**
     * Default name for the session cookie.
     */
    public const string DEFAULT_NAME = '_session';

    /**
     * The name of the cookie.
     *
     * Defaults to {@see CookieConfiguration::DEFAULT_NAME}.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The lifetime of the cookie in seconds.
     *
     * @var int|null
     */
    public null|int $lifetime;

    /**
     * The path on the server in which the cookie will be available.
     *
     * @var string|null
     */
    public null|string $path;

    /**
     * The domain that the cookie is available to.
     *
     * @var string|null
     */
    public null|string $domain;

    /**
     * Indicates whether the cookie should only be transmitted over secure HTTPS connections.
     *
     * @var bool|null
     */
    public null|bool $secure;

    /**
     * Indicates whether the cookie should be accessible only through the HTTP protocol.
     *
     * @var bool|null
     */
    public null|bool $httpOnly;

    /**
     * The SameSite attribute of the cookie.
     *
     * @var CookieSameSite|null
     */
    public null|CookieSameSite $sameSite;

    /**
     * Creates a new instance of the {@see CookieConfiguration} class.
     *
     * @param non-empty-string $name The name of the cookie.
     * @param int|null $lifetime The lifetime of the cookie in seconds.
     * @param string|null $path The path on the server in which the cookie will be available.
     * @param string|null $domain The domain that the cookie is available to.
     * @param bool|null $secure Indicates whether the cookie should only be transmitted over secure HTTPS connections.
     * @param bool|null $httpOnly Indicates whether the cookie should be accessible only through the HTTP protocol.
     * @param CookieSameSite|null $sameSite The SameSite attribute of the cookie.
     */
    public function __construct(string $name = self::DEFAULT_NAME, null|int $lifetime = null, null|string $path = null, null|string $domain = null, null|bool $secure = null, null|bool $httpOnly = null, null|CookieSameSite $sameSite = null)
    {
        $this->name     = $name;
        $this->lifetime = $lifetime;
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    /**
     * Calculates the expiration time of the cookie based on the session's age.
     *
     * @param SessionInterface $session The session object.
     *
     * @return positive-int|null The expiration time in seconds or null if the cookie is session-based.
     */
    public function getExpires(SessionInterface $session): null|int
    {
        $duration = $this->lifetime;
        if ($session->has(Session::SESSION_AGE_KEY)) {
            $duration = $session->age();
        }

        if ($duration <= 0) {
            return null;
        }

        return $duration;
    }
}
