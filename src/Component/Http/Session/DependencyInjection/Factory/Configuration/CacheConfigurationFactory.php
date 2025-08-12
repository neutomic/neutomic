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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Configuration;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;
use Override;

/**
 * @implements FactoryInterface<CacheConfiguration>
 *
 * @psalm-type ConfigurationType = array{
 *      expires?: int,
 *      limiter?: CacheLimiter,
 * }
 */
final readonly class CacheConfigurationFactory implements FactoryInterface
{
    private int $expires;

    private null|CacheLimiter $limiter;

    /**
     * @param int|null $expires
     * @param CacheLimiter|string|null $limiter
     */
    public function __construct(null|int $expires = null, null|CacheLimiter|string $limiter = null)
    {
        if (null !== $limiter && !$limiter instanceof CacheLimiter) {
            $limiter = CacheLimiter::from($limiter);
        }

        $this->expires = $expires ?? CacheConfiguration::DEFAULT_CACHE_EXPIRE;
        $this->limiter = $limiter;
    }

    #[Override]
    public function __invoke(ContainerInterface $container): CacheConfiguration
    {
        return new CacheConfiguration(
            expires: $this->expires,
            limiter: $this->limiter,
        );
    }
}
