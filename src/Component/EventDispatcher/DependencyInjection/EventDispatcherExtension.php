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

namespace Neu\Component\EventDispatcher\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\EventDispatcher\DependencyInjection\Factory\EventDispatcherFactory;
use Neu\Component\EventDispatcher\DependencyInjection\Factory\Listener\Registry\RegistryFactory;
use Neu\Component\EventDispatcher\DependencyInjection\Hook\RegisterListenersHook;
use Neu\Component\EventDispatcher\DependencyInjection\Processor\EventDispatcherAwareProcessor;
use Neu\Component\EventDispatcher\EventDispatcher;
use Neu\Component\EventDispatcher\EventDispatcherAwareInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\EventDispatcher\Listener\Registry\Registry;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface;
use Psl\Type;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

/**
 * A container extension for the event dispatcher component.
 *
 * @psalm-type ProcessorsConfiguration = array{
 *     event-dispatcher-aware?: array{
 *         event-dispatcher?: non-empty-string
 *     }
 * }
 * @psalm-type HooksConfiguration = array{
 *     register-listeners?: array{
 *         registry?: non-empty-string,
 *     }
 * }
 * @psalm-type Configuration = array{
 *      registry?: non-empty-string,
 *      processors?: ProcessorsConfiguration,
 *      hooks?: HooksConfiguration,
 * }
 */
final readonly class EventDispatcherExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $configurations = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('event-dispatcher', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(Registry::class, new RegistryFactory()));
        $container->addDefinition(Definition::ofType(EventDispatcher::class, new EventDispatcherFactory(
            $configurations['registry'] ?? null,
        )));

        $container->getDefinition(Registry::class)->addAlias(RegistryInterface::class);
        $container->getDefinition(EventDispatcher::class)->addAlias(EventDispatcherInterface::class);
        $container->getDefinition(EventDispatcher::class)->addAlias(PsrEventDispatcherInterface::class);

        $container->addProcessorForInstanceOf(EventDispatcherAwareInterface::class, new EventDispatcherAwareProcessor(
            $configurations['processors']['event-dispatcher-aware']['event-dispatcher'] ?? null,
        ));

        $container->addHook(new RegisterListenersHook(
            $configurations['hooks']['register-listeners']['registry'] ?? null,
        ));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'registry' => Type\optional(Type\non_empty_string()),
            'processors' => Type\optional(Type\shape([
                'event-dispatcher-aware' => Type\optional(Type\shape([
                    'event-dispatcher' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
            'hooks' => Type\optional(Type\shape([
                'register-listeners' => Type\optional(Type\shape([
                    'registry' => Type\optional(Type\non_empty_string()),
                ])),
            ])),
        ]);
    }
}
