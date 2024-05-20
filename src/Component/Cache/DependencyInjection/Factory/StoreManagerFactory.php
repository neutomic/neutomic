<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection\Factory;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\Cache\StoreManager;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see StoreManager} instance.
 *
 * @implements FactoryInterface<StoreManager>
 */
final readonly class StoreManagerFactory implements FactoryInterface
{
    /**
     * The identifier for the default store.
     */
    private string $defaultStoreId;

    /**
     * An array of container services identifiers, indexed by the locator service identifier.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $services;

    /**
     * Create a new {@see StoreManagerFactory} instance.
     *
     * @param string $defaultStoreId The identifier for the default store.
     * @param array<non-empty-string, non-empty-string> $services An array of container services identifiers, indexed by the locator service identifier.
     */
    public function __construct(string $defaultStoreId, array $services)
    {
        $this->services = $services;
        $this->defaultStoreId = $defaultStoreId;
    }

    public function __invoke(ContainerInterface $container): object
    {
        $locator = $container->getLocator(StoreInterface::class, $this->services);
        if (!$locator->has($this->defaultStoreId)) {
            throw new RuntimeException('The default store is not defined.');
        }

        return new StoreManager($this->defaultStoreId, $locator);
    }
}
