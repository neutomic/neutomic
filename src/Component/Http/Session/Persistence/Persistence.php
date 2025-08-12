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

use Neu\Component\Http\Message\Cookie;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Exception\InvalidIdentifierException;
use Neu\Component\Http\Session\Session;
use Neu\Component\Http\Session\Handler\HandlerInterface;
use Psl\Str;
use Psl\Env;
use Psl\DateTime;

use function Amp\File\getModificationTime;

/**
 * The session persistence mechanism.
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
     * The session handler.
     *
     * @var HandlerInterface
     */
    private HandlerInterface $handler;

    /**
     * The session cookie configuration.
     *
     * @var CookieConfiguration
     */
    protected readonly CookieConfiguration $cookie;

    /**
     * The session cache configuration.
     *
     * @var CacheConfiguration
     */
    protected readonly CacheConfiguration $cache;

    /**
     * Create a new {@see Persistence} instance.
     *
     * @param HandlerInterface $handler The session handler.
     * @param CookieConfiguration $cookie The session cookie configuration.
     * @param CacheConfiguration $cache The session cache configuration.
     */
    public function __construct(HandlerInterface $handler, CookieConfiguration $cookie, CacheConfiguration $cache)
    {
        $this->handler = $handler;
        $this->cookie = $cookie;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function initialize(Context $context, RequestInterface $request): RequestInterface
    {
        $values = $request->getCookie($this->cookie->name);
        if (null === $values) {
            return $request->withSession(new Session([]));
        }

        /** @var non-empty-string $identifier */
        $identifier = $values[0];

        try {
            $session = $this->handler->load($identifier);
        } catch (InvalidIdentifierException) {
            $session = new Session([]);
        }

        return $request->withSession($session);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function persist(Context $context, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Check if session exists in the request
        if (!$request->hasSession()) {
            return $response;
        }

        /** @psalm-suppress MissingThrowsDocblock */
        $session = $request->getSession();
        $identifier = $session->getId();

        // If session identifier is null and session has no changes, return response
        if (null === $identifier && (0 === count($session->all()) || !$session->hasChanges())) {
            return $response;
        }

        // Flush session data if session is flagged for flushing
        if ($session->isFlushed()) {
            if ($identifier !== null) {
                $this->handler->flush($identifier);
            }

            // Return response with expired cookie
            return $response->withCookie($this->cookie->name, $this->createExpiredCookie());
        }

        // Calculate expiration time for the cookie
        $expires = $this->cookie->getExpires($session);

        // Write session data to storage
        $identifier = $this->handler->save($session, $expires);

        // Return response with updated cookie
        return $this->withCacheHeaders(
            $response->withCookie($this->cookie->name, $this->createCookie($identifier, $expires))
        );
    }

    /**
     * Create the session cookie.
     *
     * @param non-empty-string $identifier The session identifier.
     * @param null|int $expires The expiration time for the cookie.
     *
     * @return Cookie The session cookie.
     */
    private function createCookie(string $identifier, null|int $expires): Cookie
    {
        return (new Cookie(value: $identifier))
            ->withExpires(($expires !== null && $expires > 0) ? $expires : null)
            ->withDomain($this->cookie->domain)
            ->withPath($this->cookie->path)
            ->withHttpOnly($this->cookie->httpOnly)
            ->withSecure($this->cookie->secure)
            ->withSameSite($this->cookie->sameSite);
    }

    /**
     * Create an expired cookie.
     *
     * @return Cookie The expired cookie.
     */
    private function createExpiredCookie(): Cookie
    {
        return (new Cookie(value: ''))
            ->withExpires(0)
            ->withDomain($this->cookie->domain)
            ->withPath($this->cookie->path)
            ->withHttpOnly($this->cookie->httpOnly)
            ->withSecure($this->cookie->secure)
            ->withSameSite($this->cookie->sameSite);
    }

    /**
     * Add cache headers to the response.
     *
     * @param ResponseInterface $response The response to add cache headers to.
     *
     * @return ResponseInterface The response with cache headers.
     */
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

    /**
     * Determine if the response already has cache headers.
     *
     * @param ResponseInterface $response
     *
     * @return bool True if the response already has cache headers, false otherwise.
     */
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
                    DateTime\Timestamp::now()
                        ->plus(DateTime\Duration::minutes($this->cache->expires))
                        ->format('EEE, dd MMM yyyy HH:mm:ss \'GMT\''),
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
            static::$lastModified = DateTime\Timestamp::fromParts(
                seconds: getModificationTime(static::$pathTranslated),
            )->format('EEE, dd MMM yyyy HH:mm:ss \'GMT\'');
        }

        $headers['Last-Modified'][] = static::$lastModified;

        /** @var non-empty-array<non-empty-string, non-empty-list<non-empty-string>> */
        return $headers;
    }
}
