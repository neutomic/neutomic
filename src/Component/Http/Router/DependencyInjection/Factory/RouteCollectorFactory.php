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

namespace Neu\Component\Http\Router\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Registry\RegistryInterface;
use Neu\Component\Http\Router\RouteCollector;

/**
 * A factory for creating a route collector.
 *
 * @implements FactoryInterface<RouteCollector>
 */
final readonly class RouteCollectorFactory implements FactoryInterface
{
    /**
     * The registry service identifier.
     *
     * @var non-empty-string
     */
    private string $registry;

    /**
     * Construct a new {@see RouteCollectorFactory} instance.
     *
     * @param non-empty-string|null $registry The registry service identifier.
     */
    public function __construct(null|string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        $registry = $container->getTyped($this->registry, RegistryInterface::class);

        return new RouteCollector($registry);
    }
}
