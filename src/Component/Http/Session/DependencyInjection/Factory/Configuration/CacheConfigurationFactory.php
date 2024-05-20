<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Configuration;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CacheLimiter;

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
    private ?CacheLimiter $limiter;

    /**
     * @param ConfigurationType $config
     */
    public function __construct(?int $expires = null, ?CacheLimiter $limiter = null)
    {
        $this->expires = $expires ?? CacheConfiguration::DEFAULT_CACHE_EXPIRE;
        $this->limiter = $limiter;
    }

    public function __invoke(ContainerInterface $container): CacheConfiguration
    {
        return new CacheConfiguration(
            expires: $this->expires,
            limiter: $this->limiter,
        );
    }
}
