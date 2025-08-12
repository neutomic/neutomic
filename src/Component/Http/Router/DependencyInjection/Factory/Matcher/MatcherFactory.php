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

namespace Neu\Component\Http\Router\DependencyInjection\Factory\Matcher;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\Matcher\Matcher;
use Neu\Component\Http\Router\Registry\RegistryInterface;

/**
 * Factory for creating a router matcher.
 *
 * @implements FactoryInterface<Matcher>
 */
final readonly class MatcherFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $registry;

    /**
     * Create a new {@see MatcherFactory} instance.
     *
     * @param non-empty-string|null $registry The registry service identifier.
     */
    public function __construct(null|string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        return new Matcher(
            $container->getTyped($this->registry, RegistryInterface::class),
        );
    }
}
