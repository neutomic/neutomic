<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session\Configuration;

final readonly class CacheConfiguration
{
    /**
     * Default cache expire time in minutes.
     *
     * @link https://www.php.net/manual/en/session.configuration.php#ini.session.cache-expire
     */
    public const int DEFAULT_CACHE_EXPIRE = 180;

    /**
     * This unusual past date value is taken from the php session extension source code and used "as is" for consistency.
     *
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1204
     * @link https://github.com/php/php-src/blob/e17fd1f2d95f081536cb2c02a874f286d7a82ace/ext/session/session.c#L1211
     */
    public const string CACHE_PAST_DATE = 'Thu, 19 Nov 1981 08:52:00 GMT';

    /**
     * The number of minutes after which data will be seen as 'garbage' and cleaned up.
     *
     * Defaults to {@see CacheConfiguration::DEFAULT_CACHE_EXPIRE}.
     *
     * @var int
     */
    public int $expires;

    /**
     * The cache limiter to use.
     *
     * @var CacheLimiter|null
     */
    public ?CacheLimiter $limiter;

    public function __construct(int $expires = self::DEFAULT_CACHE_EXPIRE, ?CacheLimiter $limiter = null)
    {
        $this->expires = $expires;
        $this->limiter = $limiter;
    }
}
