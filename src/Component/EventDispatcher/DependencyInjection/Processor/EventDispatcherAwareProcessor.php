<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher\DependencyInjection\Processor;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\ProcessorInterface;
use Neu\Component\EventDispatcher\EventDispatcherAwareInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Processor for injecting the event dispatcher into services implementing {@see EventDispatcherAwareInterface}.
 */
final class EventDispatcherAwareProcessor implements ProcessorInterface
{
    private string $eventDispatcher;

    /**
     * Creates a new {@see EventDispatcherAwareProcessor} instance.
     *
     * @param string|null $eventDispatcher The event dispatcher service identifier, defaults to {@see EventDispatcherInterface::class}.
     */
    public function __construct(?string $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerInterface $container, DefinitionInterface $definition, object $service): object
    {
        if ($service instanceof EventDispatcherAwareInterface) {
            $service->setEventDispatcher($container->getTyped(
                $this->eventDispatcher,
                EventDispatcherInterface::class,
            ));
        }

        return $service;
    }
}
