<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Generator;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Generator\Generator;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;

/**
 * Factory for creating a router generator.
 *
 * @implements FactoryInterface<Generator>
 */
final readonly class GeneratorFactory implements FactoryInterface
{
    private string $registry;

    public function __construct(?string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new Generator(
            $container->getTyped($this->registry, RegistryInterface::class),
        );
    }
}
