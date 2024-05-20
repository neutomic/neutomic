<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Matcher;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Matcher\Matcher;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;

/**
 * Factory for creating a router matcher.
 *
 * @implements FactoryInterface<Matcher>
 */
final readonly class MatcherFactory implements FactoryInterface
{
    private string $registry;
    private string $store;

    public function __construct(?string $registry = null, ?string $store = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
        $this->store = $store ?? StoreInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new Matcher(
            $container->getTyped($this->registry, RegistryInterface::class),
            $container->getTyped($this->store, StoreInterface::class),
        );
    }
}
