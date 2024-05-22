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

namespace Neu\Component\Http\Session\Persistence;

use Amp\File;
use Neu\Component\Http\Message\Cookie;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\SessionInterface;
use Neu\Component\Http\Session\Storage\StorageInterface;
use Psl\Env;
use Psl\Math;
use Psl\Str;

use function Amp\Http\formatDateHeader;
use function time;

/**
 * Implements the {@see PersistenceInterface} and handles the persistence of session data.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class Persistence implements PersistenceInterface
{
    /**
     * The path translated.
     */
    private static null|string $pathTranslated = null;

    /**
     * The last modified date.
     */
    private static null|string $lastModified = null;

    /**
     * @var StorageInterface The session storage interface.
     */
    private readonly StorageInterface $storage;

    /**
     * The session cookie configuration.
     *
     * @param CookieConfiguration $cookie
     */
    private readonly CookieConfiguration $cookie;

    /**
     * The session cache configuration.
     *
     * @param CacheConfiguration $cache
     */
    private readonly CacheConfiguration $cache;

    /**
     * Creates a new {@see Persistence} instance.
     *
     * @param StorageInterface $storage The session storage.
     * @param CookieConfiguration $cookie The session cookie configuration.
     * @param CacheConfiguration $cache The session cache configuration.
     */
    public function __construct(StorageInterface $storage, CookieConfiguration $cookie, CacheConfiguration $cache)
    {
        $this->storage = $storage;
        $this->cookie = $cookie;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function persist(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Check if session exists in the request
        if (!$request->hasSession()) {
            return $response;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $session = $request->getSession();
        $id = $session->getId();

        // If session ID is empty and session has no changes, return response
        if (null === $id && (0 === count($session->all()) || !$session->hasChanges())) {
            return $response;
        }

        // Flush session data if session is flagged for flushing
        if ($session->isFlushed()) {
            if ($id !== null) {
                $this->storage->flush($id);
            }

            // Return response with expired cookie
            return $response->withCookie($this->cookie->name, new Cookie('', expires: 0));
        }

        // Calculate expiration time for the cookie
        $expires = $this->cookie->getExpires($session);

        // Write session data to storage
        $id = $this->storage->write($session, $expires);

        // Return response with updated cookie
        return $this->withCacheHeaders(
            $response->withCookie($this->cookie->name, $this->createCookie($id, $expires))
        );
    }

    /**
     * Calculates the persistence duration for the session.
     *
     * @param SessionInterface $session The session object.
     *
     * @return int The persistence duration in seconds.
     */
    protected function getPersistenceDuration(SessionInterface $session): int
    {
        $duration = $this->cookie->lifetime ?? 0;
        if ($session->has(Session::SESSION_AGE_KEY)) {
            $duration = $session->age();
        }

        return Math\maxva($duration, 0);
    }

    private function createCookie(string $id, null|int $expires): Cookie
    {
        return (new Cookie(value: $id))
            ->withExpires(($expires !== null && $expires > 0) ? $expires : null)
            ->withDomain($this->cookie->domain)
            ->withPath($this->cookie->path)
            ->withHttpOnly($this->cookie->httpOnly)
            ->withSecure($this->cookie->secure)
            ->withSameSite($this->cookie->sameSite);
    }

    private function withCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        $cacheLimiter = $this->cache->limiter;
        if ($cacheLimiter === null || $this->responseAlreadyHasCacheHeaders($response)) {
            return $response;
        }

        $headers = $this->generateCacheHeaders($cacheLimiter);
        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }

    private function responseAlreadyHasCacheHeaders(ResponseInterface $response): bool
    {
        return (
            $response->hasHeader('Expires') ||
            $response->hasHeader('Last-Modified') ||
            $response->hasHeader('Cache-Control') ||
            $response->hasHeader('Pragma')
        );
    }

    /**
     * @return non-empty-array<non-empty-string, non-empty-list<non-empty-string>>
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function generateCacheHeaders(CacheLimiter $limiter): array
    {
        return match ($limiter) {
            CacheLimiter::NoCache => [
                'Expires' => [CacheConfiguration::CACHE_PAST_DATE],
                'Cache-Control' => ['no-store'],
            ],
            CacheLimiter::Public => $this->withLastModifiedAndMaxAge([
                'Expires' => [
                    formatDateHeader(time() + (60 * $this->cache->expires)),
                ],
                'Cache-Control' => ['public'],
            ]),
            CacheLimiter::Private => $this->withLastModifiedAndMaxAge([
                'Expires' => [CacheConfiguration::CACHE_PAST_DATE],
                'Cache-Control' => ['private'],
            ]),
            CacheLimiter::PrivateNoExpire => $this->withLastModifiedAndMaxAge([
                'Cache-Control' => ['private'],
            ]),
        };
    }

    /**
     * same behavior as the PHP engine ( current_exec() is used instead of Path translator variable ).
     *
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1179-L1184
     *
     * @param non-empty-array<non-empty-string, non-empty-list<non-empty-string>> $headers
     *
     * @return non-empty-array<non-empty-string, non-empty-list<non-empty-string>>
     */
    private function withLastModifiedAndMaxAge(array $headers): array
    {
        $maxAge = 60 * $this->cache->expires;
        $headers['Cache-Control'][] = Str\format('max-age=%d', $maxAge);

        if (null === static::$pathTranslated) {
            static::$pathTranslated = Env\current_exec();
        }

        if (null === static::$lastModified) {
            static::$lastModified = formatDateHeader(File\getModificationTime(static::$pathTranslated));
        }

        $headers['Last-Modified'][] = static::$lastModified;

        /** @var non-empty-array<non-empty-string, non-empty-list<non-empty-string>> */
        return $headers;
    }
}
