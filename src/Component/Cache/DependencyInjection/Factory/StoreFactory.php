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

use Neu\Component\Cache\Driver\DriverInterface;
use Neu\Component\Cache\Store;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a {@see Store} instance.
 *
 * @implements FactoryInterface<Store>
 */
final readonly class StoreFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $driver;

    /**
     * @param null|non-empty-string $driver The service identifier of the driver to use, defaults to {@see DriverInterface}.
     */
    public function __construct(null|string $driver = null)
    {
        $this->driver = $driver ?? DriverInterface::class;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): Store
    {
        return new Store(
            $container->getTyped($this->driver, DriverInterface::class)
        );
    }
}
