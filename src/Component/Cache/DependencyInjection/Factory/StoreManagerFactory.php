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

namespace Neu\Component\Cache\DependencyInjection\Factory;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\Cache\StoreManager;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

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

    #[Override]
    public function __invoke(ContainerInterface $container): object
    {
        $locator = $container->getLocator(StoreInterface::class, $this->services);
        if (!$locator->has($this->defaultStoreId)) {
            throw new RuntimeException('The default store is not defined.');
        }

        return new StoreManager($this->defaultStoreId, $locator);
    }
}
