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

namespace Neu\Component\Http\Session\DependencyInjection\Factory\Storage;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Session\Storage\Storage;

/**
 * A factory to create a storage instance.
 *
 * @implements FactoryInterface<Storage>
 */
final readonly class StorageFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $store;

    /**
     * @param non-empty-string|null $store
     */
    public function __construct(null|string $store = null)
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
