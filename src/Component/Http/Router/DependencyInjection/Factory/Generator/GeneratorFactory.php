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

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Generator;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Generator\Generator;
use Neu\Component\Http\Router\Registry\RegistryInterface;

/**
 * Factory for creating a router generator.
 *
 * @implements FactoryInterface<Generator>
 */
final readonly class GeneratorFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $registry;

    /**
     * Create a new {@see GeneratorFactory} instance.
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
    public function __invoke(ContainerInterface $container): object
    {
        return new Generator(
            $container->getTyped($this->registry, RegistryInterface::class),
        );
    }
}
