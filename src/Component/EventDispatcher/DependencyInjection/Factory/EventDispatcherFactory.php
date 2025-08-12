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

namespace Neu\Component\EventDispatcher\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcher;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface;

/**
 * A factory for creating a new instance of the {@see EventDispatcher}.
 *
 * @implements FactoryInterface<EventDispatcher>
 */
final readonly class EventDispatcherFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $registry;

    /**
     * @param non-empty-string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     */
    public function __construct(null|string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        return new EventDispatcher(
            $container->getTyped($this->registry, RegistryInterface::class),
        );
    }
}
