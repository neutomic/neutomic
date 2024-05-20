<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Persistence;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Configuration\CacheConfiguration;
use Neu\Component\Http\Session\Configuration\CookieConfiguration;
use Neu\Component\Http\Session\Persistence\Persistence;
use Neu\Component\Http\Session\Storage\StorageInterface;

/**
 * Factory for creating Persistence instances.
 *
 * @implements FactoryInterface<Persistence>
 */
final readonly class PersistenceFactory implements FactoryInterface
{
    private string $storage;
    private string $cookieConfiguration;
    private string $cacheConfiguration;

    public function __construct(?string $storage = null, ?string $cookieConfiguration = null, ?string $cacheConfiguration = null)
    {
        $this->storage = $storage ?? StorageInterface::class;
        $this->cookieConfiguration = $cookieConfiguration ?? CookieConfiguration::class;
        $this->cacheConfiguration = $cacheConfiguration ?? CacheConfiguration::class;
    }

    public function __invoke(ContainerInterface $container): Persistence
    {
        return new Persistence(
            $container->getTyped($this->storage, StorageInterface::class),
            $container->getTyped($this->cookieConfiguration, CookieConfiguration::class),
            $container->getTyped($this->cacheConfiguration, CacheConfiguration::class),
        );
    }
}
