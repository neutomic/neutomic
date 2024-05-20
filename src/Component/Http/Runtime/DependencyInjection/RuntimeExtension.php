<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\ContentDelivery\ContentDelivererFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Handler\Resolver\HandlerResolverFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\AccessLogMiddlewareFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\CompressionMiddlewareFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\MiddlewareQueueFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\RouterMiddlewareFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\SessionMiddlewareFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware\StaticContentMiddlewareFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Factory\RuntimeFactory;
use Neu\Component\Http\Runtime\DependencyInjection\Hook\EnqueueMiddlewareHook;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolver;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolverInterface;
use Neu\Component\Http\Runtime\Middleware\AccessLogMiddleware;
use Neu\Component\Http\Runtime\Middleware\CompressionMiddleware;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueue;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Neu\Component\Http\Runtime\Middleware\RouterMiddleware;
use Neu\Component\Http\Runtime\Middleware\SessionMiddleware;
use Neu\Component\Http\Runtime\Middleware\StaticContentMiddleware;
use Neu\Component\Http\Runtime\Runtime;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Psl\Iter;
use Psl\Type;

/**
 * @psalm-type Configuration = array{
 *     concurrency-limit?: int,
 *     event-dispatcher?: non-empty-string,
 *     handler-resolver?: non-empty-string,
 *     middleware-queue?: non-empty-string,
 *     recovery?: non-empty-string,
 *     handler?: array{
 *         fallback?: non-empty-string,
 *     },
 *     middleware?: array{
 *         compression?: null|array{
 *             logger?: non-empty-string,
 *             minimum-compressible-content-length?: int,
 *             compressible-content-types-regex?: non-empty-string,
 *             level?: -1|0|1|2|3|4|5|6|7|8|9,
 *             memory?: 1|2|3|4|5|6|7|8|9,
 *             window?: 8|9|10|11|12|13|14|15,
 *             priority?: int,
 *         },
 *         static-content?: null|array{
 *             priority?: int,
 *             deliverer?: non-empty-string,
 *             roots?: non-empty-array<string, string>,
 *             extensions?: non-empty-list<string>,
 *             logger?: non-empty-string,
 *         },
 *         session?: null|array{
 *             priority?: int,
 *             initializer?: non-empty-string,
 *             persistence?: non-empty-string,
 *         },
 *         router?: null|array{
 *             priority?: int,
 *             matcher?: non-empty-string,
 *         },
 *         access-log?: null|array{
 *             priority?: int,
 *             logger?: non-empty-string,
 *         },
 *     },
 *     content-delivery?: array{
 *         logger?: non-empty-string,
 *     },
 *     hooks?: array{
 *         enqueue-middleware?: array{
 *             queue: non-empty-string,
 *             ignore?: list<non-empty-string>,
 *         }
 *     }
 * }
 */
final readonly class RuntimeExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        /** @var string|null $defaultLogger */
        $defaultLogger = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('logger', Type\string(), null)
        ;

        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getContainer('http')
            ->getOfTypeOrDefault('runtime', $this->getConfigurationType(), [])
        ;

        $container->addDefinitions([
            Definition::ofType(HandlerResolver::class, new HandlerResolverFactory(
                $configuration['handler']['fallback'] ?? null,
            )),
            Definition::ofType(MiddlewareQueue::class, new MiddlewareQueueFactory()),
            Definition::ofType(Runtime::class, new RuntimeFactory(
                $configuration['concurrency-limit'] ?? null,
                $configuration['event-dispatcher'] ?? null,
                $configuration['handler-resolver'] ?? null,
                $configuration['middleware-queue'] ?? null,
                $configuration['recovery'] ?? null,
            )),
            Definition::ofType(ContentDeliverer::class, new ContentDelivererFactory(
                $configuration['content-delivery']['logger'] ?? $defaultLogger ?? null,
            )),
        ]);

        $container->getDefinition(HandlerResolver::class)->addAlias(HandlerResolverInterface::class);
        $container->getDefinition(MiddlewareQueue::class)->addAlias(MiddlewareQueueInterface::class);
        $container->getDefinition(Runtime::class)->addAlias(RuntimeInterface::class);

        $container->addHook(new EnqueueMiddlewareHook(
            $configuration['hooks']['enqueue-middleware']['queue'] ?? null,
            $configuration['hooks']['enqueue-middleware']['ignore'] ?? [],
        ));

        if (Iter\contains_key($configuration, 'middleware')) {
            if (Iter\contains_key($configuration['middleware'], 'static-content')) {
                $container->addDefinition(
                    Definition::ofType(StaticContentMiddleware::class, new StaticContentMiddlewareFactory(
                        $configuration['middleware']['static-content']['deliverer'] ?? null,
                        $configuration['middleware']['static-content']['roots'] ?? [],
                        $configuration['middleware']['static-content']['extensions'] ?? [],
                        $configuration['middleware']['static-content']['logger'] ?? $defaultLogger ?? null,
                        $configuration['middleware']['static-content']['priority'] ?? null,
                    )),
                );
            }

            if (Iter\contains_key($configuration['middleware'], 'compression')) {
                $container->addDefinition(
                    Definition::ofType(CompressionMiddleware::class, new CompressionMiddlewareFactory(
                        $configuration['middleware']['compression']['logger'] ?? $defaultLogger ?? null,
                        $configuration['middleware']['compression']['minimum-compressible-content-length'] ?? null,
                        $configuration['middleware']['compression']['compressible-content-types-regex'] ?? null,
                        $configuration['middleware']['compression']['level'] ?? null,
                        $configuration['middleware']['compression']['memory'] ?? null,
                        $configuration['middleware']['compression']['window'] ?? null,
                        $configuration['middleware']['compression']['priority'] ?? null,
                    )),
                );
            }

            if (Iter\contains_key($configuration['middleware'], 'access-log')) {
                $container->addDefinition(
                    Definition::ofType(AccessLogMiddleware::class, new AccessLogMiddlewareFactory(
                        $configuration['middleware']['access-log']['logger'] ?? $defaultLogger ?? null,
                        $configuration['middleware']['access-log']['priority'] ?? null,
                    )),
                );
            }

            if (Iter\contains_key($configuration['middleware'], 'session')) {
                $container->addDefinition(
                    Definition::ofType(SessionMiddleware::class, new SessionMiddlewareFactory(
                        $configuration['middleware']['session']['priority'] ?? null,
                        $configuration['middleware']['session']['initializer'] ?? null,
                        $configuration['middleware']['session']['persistence'] ?? null,
                    )),
                );
            }

            if (Iter\contains_key($configuration['middleware'], 'router')) {
                $container->addDefinition(
                    Definition::ofType(RouterMiddleware::class, new RouterMiddlewareFactory(
                        $configuration['middleware']['router']['priority'] ?? null,
                        $configuration['middleware']['router']['matcher'] ?? null,
                    )),
                );
            }
        }
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'concurrency-limit' => Type\optional(Type\int()),
            'event-dispatcher' => Type\optional(Type\non_empty_string()),
            'handler-resolver' => Type\optional(Type\non_empty_string()),
            'middleware-queue' => Type\optional(Type\non_empty_string()),
            'recovery' => Type\optional(Type\non_empty_string()),
            'handler' => Type\optional(Type\shape([
                'fallback' => Type\optional(Type\non_empty_string()),
            ])),
            'middleware' => Type\shape([
                'static-content' => Type\optional(Type\nullable(Type\shape([
                    'priority' => Type\optional(Type\int()),
                    'deliverer' => Type\optional(Type\non_empty_string()),
                    'roots' => Type\optional(Type\dict(Type\non_empty_string(), Type\non_empty_string())),
                    'extensions' => Type\optional(Type\vec(Type\non_empty_string())),
                    'logger' => Type\optional(Type\non_empty_string()),
                ]))),
                'session' => Type\optional(Type\nullable(Type\shape([
                    'priority' => Type\optional(Type\int()),
                    'initializer' => Type\optional(Type\non_empty_string()),
                    'persistence' => Type\optional(Type\non_empty_string()),
                ]))),
                'router' => Type\optional(Type\nullable(Type\shape([
                    'priority' => Type\optional(Type\int()),
                    'matcher' => Type\optional(Type\non_empty_string()),
                ]))),
                'compression' => Type\optional(Type\nullable(Type\shape([
                    'logger' => Type\optional(Type\non_empty_string()),
                    'minimum-compressible-content-length' => Type\optional(Type\int()),
                    'compressible-content-types-regex' => Type\optional(Type\non_empty_string()),
                    'level' => Type\optional(Type\union(
                        Type\literal_scalar(-1),
                        Type\literal_scalar(0),
                        Type\literal_scalar(1),
                        Type\literal_scalar(2),
                        Type\literal_scalar(3),
                        Type\literal_scalar(4),
                        Type\literal_scalar(5),
                        Type\literal_scalar(6),
                        Type\literal_scalar(7),
                        Type\literal_scalar(8),
                        Type\literal_scalar(9),
                    )),
                    'memory' => Type\optional(Type\union(
                        Type\literal_scalar(1),
                        Type\literal_scalar(2),
                        Type\literal_scalar(3),
                        Type\literal_scalar(4),
                        Type\literal_scalar(5),
                        Type\literal_scalar(6),
                        Type\literal_scalar(7),
                        Type\literal_scalar(8),
                        Type\literal_scalar(9),
                    )),
                    'window' => Type\optional(Type\union(
                        Type\literal_scalar(8),
                        Type\literal_scalar(9),
                        Type\literal_scalar(10),
                        Type\literal_scalar(11),
                        Type\literal_scalar(12),
                        Type\literal_scalar(13),
                        Type\literal_scalar(14),
                        Type\literal_scalar(15),
                    )),
                    'priority' => Type\optional(Type\int()),
                ]))),
                'access-log' => Type\optional(Type\nullable(Type\shape([
                    'priority' => Type\optional(Type\int()),
                    'logger' => Type\optional(Type\non_empty_string()),
                ]))),
            ]),
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
}
