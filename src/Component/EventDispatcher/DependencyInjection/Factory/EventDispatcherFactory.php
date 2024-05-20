<?php

declare(strict_types=1);

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
    private string $registry;

    /**
     * @param string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     */
    public function __construct(?string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        return new EventDispatcher(
            $container->getTyped($this->registry, RegistryInterface::class),
        );
    }
}
