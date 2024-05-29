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

namespace Neu\Component\Http\Runtime\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\ContentDelivery\ContentDelivererFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Handler\Resolver\HandlerResolverFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\MiddlewareQueueFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\RuntimeFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Hook\EnqueueMiddlewareHook;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolver;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolverInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueue;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Neu\Component\Http\Runtime\Runtime;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Psl\Type;

/**
 * A dependency injection extension for the HTTP runtime.
 *
 * @psalm-type Configuration = array{
 *     concurrency-limit?: positive-int,
 *     event-dispatcher?: non-empty-string,
 *     handler-resolver?: non-empty-string,
 *     middleware-queue?: non-empty-string,
 *     recovery?: non-empty-string,
 *     handler?: array{
 *         fallback?: non-empty-string,
 *     },
 *     content-delivery?: array{
 *         logger?: non-empty-string,
 *     },
 *     hooks?: array{
 *         enqueue-middleware?: array{
 *             queue?: non-empty-string,
 *             ignore?: list<non-empty-string>,
 *         }
 *     }
 * }
 */
final readonly class RuntimeExtension implements ExtensionInterface
{
    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'concurrency-limit' => Type\optional(Type\positive_int()),
            'event-dispatcher' => Type\optional(Type\non_empty_string()),
            'handler-resolver' => Type\optional(Type\non_empty_string()),
            'middleware-queue' => Type\optional(Type\non_empty_string()),
            'recovery' => Type\optional(Type\non_empty_string()),
            'handler' => Type\optional(Type\shape([
                'fallback' => Type\optional(Type\non_empty_string()),
            ])),
            'content-delivery' => Type\optional(Type\shape([
                'logger' => Type\optional(Type\non_empty_string()),
            ])),
            'hooks' => Type\optional(Type\shape([
                'enqueue-middleware' => Type\optional(Type\shape([
                    'queue' => Type\optional(Type\non_empty_string()),
                    'ignore' => Type\optional(Type\vec(Type\non_empty_string())),
                ])),
            ])),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $defaultLogger = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('logger', Type\non_empty_string(), null)
        ;

        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('runtime', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(HandlerResolver::class, new HandlerResolverFactory(
            fallback: $configuration['handler']['fallback'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(MiddlewareQueue::class, new MiddlewareQueueFactory()));

        $container->addDefinition(Definition::ofType(Runtime::class, new RuntimeFactory(
            eventDispatcher: $configuration['event-dispatcher'] ?? null,
            handlerResolver: $configuration['handler-resolver'] ?? null,
            middlewareQueue: $configuration['middleware-queue'] ?? null,
            recovery: $configuration['recovery'] ?? null,
            concurrencyLimit: $configuration['concurrency-limit'] ?? null,
        )));

        $container->addDefinition(Definition::ofType(ContentDeliverer::class, new ContentDelivererFactory(
            logger: $configuration['content-delivery']['logger'] ?? $defaultLogger ?? null,
        )));

        $container->getDefinition(HandlerResolver::class)->addAlias(HandlerResolverInterface::class);
        $container->getDefinition(MiddlewareQueue::class)->addAlias(MiddlewareQueueInterface::class);
        $container->getDefinition(Runtime::class)->addAlias(RuntimeInterface::class);

        $container->addHook(new EnqueueMiddlewareHook(
            queue: $configuration['hooks']['enqueue-middleware']['queue'] ?? null,
            ignore: $configuration['hooks']['enqueue-middleware']['ignore'] ?? [],
        ));
    }
}
