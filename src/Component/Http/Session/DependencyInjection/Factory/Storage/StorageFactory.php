<?php

declare(strict_types=1);

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Storage;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Storage\Storage;

/**
 * @implements FactoryInterface<Storage>
 */
final readonly class StorageFactory implements FactoryInterface
{
    private string $store;

    public function __construct(?string $store = null)
    {
        $this->store = $store ?? StoreInterface::class;
    }

    public function __invoke(ContainerInterface $container): Storage
    {
        return new Storage(
            $container->getTyped($this->store, StoreInterface::class)
        );
    }
}
