<?php

declare(strict_types=1);

namespace Neu\Component\Cache;

use Neu\Component\Cache\Exception\RuntimeException;
use Neu\Component\Cache\Exception\StoreNotFoundException;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Neu\Component\DependencyInjection\ServiceLocatorInterface;

final class StoreManager implements StoreManagerInterface
{
    /**
     * The identifier for the default store, or null if there is no default store.
     */
    private string $defaultStoreId;

    /**
     * The service locator used to create store instances.
     *
     * @var ServiceLocatorInterface<StoreInterface>
     */
    private ServiceLocatorInterface $locator;

    /**
     * Create a new {@see StoreManager} instance.
     *
     * @param string $defaultStoreId The identifier for the default store.
     * @param ServiceLocatorInterface<StoreInterface> $locator The service locator used to create store instances.
     */
    public function __construct(string $defaultStoreId, ServiceLocatorInterface $locator)
    {
        $this->defaultStoreId = $defaultStoreId;
        $this->locator = $locator;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStore(): StoreInterface
    {
        return $this->getStore($this->defaultStoreId);
    }

    /**
     * @inheritDoc
     */
    public function hasStore(string $identifier): bool
    {
        return $this->locator->has($identifier);
    }

    /**
     * @inheritDoc
     */
    public function getStore(string $identifier): StoreInterface
    {
        try {
            return $this->locator->get($identifier);
        } catch (ServiceNotFoundException $exception) {
            throw StoreNotFoundException::forStore($identifier, $exception);
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException('An error occurred while retrieving the store.', previous: $exception);
        }
    }
}
