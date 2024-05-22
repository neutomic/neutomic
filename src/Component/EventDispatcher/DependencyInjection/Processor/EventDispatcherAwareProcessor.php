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
    /**
     * @var non-empty-string
     */
    private string $eventDispatcher;

    /**
     * Creates a new {@see EventDispatcherAwareProcessor} instance.
     *
     * @param non-empty-string|null $eventDispatcher The event dispatcher service identifier, defaults to {@see EventDispatcherInterface::class}.
     */
    public function __construct(null|string $eventDispatcher = null)
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
