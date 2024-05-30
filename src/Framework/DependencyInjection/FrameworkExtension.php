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

namespace Neu\Framework\DependencyInjection;

use Neu\Component\Advisory\DependencyInjection\AdvisoryExtension;
use Neu\Component\Cache\DependencyInjection\CacheExtension;
use Neu\Component\Console\DependencyInjection\ConsoleExtension;
use Neu\Component\Csrf\DependencyInjection\CsrfExtension;
use Neu\Component\DependencyInjection\CompositeExtensionInterface;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\EventDispatcher\DependencyInjection\EventDispatcherExtension;
use Neu\Component\Http\DependencyInjection\HttpExtension;
use Neu\Framework\Command;
use Neu\Framework\Listener;
use Neu\Framework\Middleware;
use Psl\Type;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The framework extension.
 *
 * @psalm-type CommandsConfiguration = array{
 *     advisory?: array{
 *         advice?: false|array{
 *             advisory?: non-empty-string,
 *         }
 *     },
 *     http?: array{
 *         server?: array{
 *             start?: false|array<array-key, mixed>,
 *             cluster?: false|array{
 *                 watch?: array{
 *                     interval?: float,
 *                     directories?: list<non-empty-string>,
 *                     extensions?: list<non-empty-string>,
 *                 }
 *             }
 *         }
 *     }
 * }
 * @psalm-type ListenersConfiguration = array{
 *     advisory?: array{
 *         before-execute?: false|array{
 *             advisory?: non-empty-string,
 *         }
 *     },
 *     broadcast?: array{
 *         server-stopping?: false|array{
 *             hub-manager?: non-empty-string,
 *         }
 *     },
 *     cache?: array{
 *         server-stopping?: false|array{
 *             store-manager?: non-empty-string,
 *         }
 *     },
 *     database?: array{
 *         server-stopping?: false|array{
 *             database-manager?: non-empty-string,
 *         }
 *     }
 * }
 * @psalm-type MiddlewareConfiguration = array{
 *     x-powered-by?: false|array{
 *         powered-by?: non-empty-string,
 *         expose-php-version?: bool,
 *     },
 *     access-log?: false|array{
 *         logger?: non-empty-string,
 *         priority?: int,
 *     },
 *     router?: false|array{
 *         matcher?: non-empty-string,
 *         priority?: int,
 *     },
 *     session?: false|array{
 *         initializer?: non-empty-string,
 *         persistence?: non-empty-string,
 *         priority?: int,
 *     },
 *     compression?: false|array{
 *         logger?: non-empty-string,
 *         minimum-compressible-content-length?: positive-int,
 *         compressible-content-types-regex?: non-empty-string,
 *         level?: -1|0|1|2|3|4|5|6|7|8|9,
 *         memory?: 1|2|3|4|5|6|7|8|9,
 *         window?: 8|9|10|11|12|13|14|15,
 *         priority?: int,
 *     },
 *     static-content?: false|array{
 *         deliverer?: non-empty-string,
 *         roots?: array<non-empty-string, non-empty-string>,
 *         extensions?: list<non-empty-string>,
 *         logger?: non-empty-string,
 *         priority?: int,
 *     },
 * }
 * @psalm-type Configuration = array{
 *     commands?: CommandsConfiguration,
 *     listeners?: ListenersConfiguration,
 *     middleware?: MiddlewareConfiguration,
 * }
 */
final readonly class FrameworkExtension implements CompositeExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        $configuration = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('framework', $this->getConfigurationType(), [])
        ;

        $advisoryAdviceCommandConfiguration = $configuration['commands']['advisory']['advice'] ?? [];
        if (false !== $advisoryAdviceCommandConfiguration) {
            $container->addDefinition(Definition::ofType(
                Command\Advisory\AdviceCommand::class,
                new Factory\Command\Advisory\AdviceCommandFactory(
                    advisory: $advisoryAdviceCommandConfiguration['advisory'] ?? null,
                ),
            ));
        }

        $httpServerStartCommandConfiguration = $configuration['commands']['http']['server']['start'] ?? [];
        if (false !== $httpServerStartCommandConfiguration) {
            $container->addDefinition(Definition::ofType(
                Command\Http\Server\StartCommand::class,
                new Factory\Command\Http\Server\StartCommandFactory(),
            ));
        }

        $httpServerClusterCommandConfiguration = $configuration['commands']['http']['server']['cluster'] ?? [];
        if (false !== $httpServerClusterCommandConfiguration) {
            $container->addDefinition(Definition::ofType(
                Command\Http\Server\ClusterCommand::class,
                new Factory\Command\Http\Server\ClusterCommandFactory(
                    watchInterval: $httpServerClusterCommandConfiguration['watch']['interval'] ?? null,
                    watchDirectories: $httpServerClusterCommandConfiguration['watch']['directories'] ?? null,
                    watchExtensions: $httpServerClusterCommandConfiguration['watch']['extensions'] ?? null,
                ),
            ));
        }

        $advisoryBeforeExecuteEventListenerConfiguration = $configuration['listeners']['advisory']['before-execute'] ?? [];
        if (false !== $advisoryBeforeExecuteEventListenerConfiguration) {
            $container->addDefinition(Definition::ofType(
                Listener\Advisory\BeforeExecuteEventListener::class,
                new Factory\Listener\Advisory\BeforeExecuteEventListenerFactory(
                    advisory: $advisoryBeforeExecuteEventListenerConfiguration['advisory'] ?? null,
                ),
            ));
        }

        $broadcastServerStoppingEventListenerConfiguration = $configuration['listeners']['broadcast']['server-stopping'] ?? [];
        if (false !== $broadcastServerStoppingEventListenerConfiguration) {
            $container->addDefinition(Definition::ofType(
                Listener\Broadcast\ServerStoppingEventListener::class,
                new Factory\Listener\Broadcast\ServerStoppingEventListenerFactory(
                    hubManager: $broadcastServerStoppingEventListenerConfiguration['hub-manager'] ?? null,
                ),
            ));
        }

        $cacheServerStoppingEventListenerConfiguration = $configuration['listeners']['cache']['server-stopping'] ?? [];
        if (false !== $cacheServerStoppingEventListenerConfiguration) {
            $container->addDefinition(Definition::ofType(
                Listener\Cache\ServerStoppingEventListener::class,
                new Factory\Listener\Cache\ServerStoppingEventListenerFactory(
                    storeManager: $cacheServerStoppingEventListenerConfiguration['store-manager'] ?? null,
                ),
            ));
        }

        $databaseServerStoppingEventListenerConfiguration = $configuration['listeners']['database']['server-stopping'] ?? [];
        if (false !== $databaseServerStoppingEventListenerConfiguration) {
            $container->addDefinition(Definition::ofType(
                Listener\Database\ServerStoppingEventListener::class,
                new Factory\Listener\Database\ServerStoppingEventListenerFactory(
                    databaseManager: $databaseServerStoppingEventListenerConfiguration['database-manager'] ?? null,
                ),
            ));
        }

        $xPoweredByMiddlewareConfiguration = $configuration['middleware']['x-powered-by'] ?? [];
        if (false !== $xPoweredByMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\XPoweredByMiddleware::class,
                new Factory\Middleware\XPoweredByMiddlewareFactory(
                    poweredBy: $xPoweredByMiddlewareConfiguration['powered-by'] ?? null,
                    exposePhpVersion: $xPoweredByMiddlewareConfiguration['expose-php-version'] ?? null,
                ),
            ));
        }

        $accessLogMiddlewareConfiguration = $configuration['middleware']['access-log'] ?? [];
        if (false !== $accessLogMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\AccessLogMiddleware::class,
                new Factory\Middleware\AccessLogMiddlewareFactory(
                    logger: $accessLogMiddlewareConfiguration['logger'] ?? null,
                    priority: $accessLogMiddlewareConfiguration['priority'] ?? null,
                ),
            ));
        }

        $routerMiddlewareConfiguration = $configuration['middleware']['router'] ?? [];
        if (false !== $routerMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\RouterMiddleware::class,
                new Factory\Middleware\RouterMiddlewareFactory(
                    matcher: $routerMiddlewareConfiguration['matcher'] ?? null,
                    priority: $routerMiddlewareConfiguration['priority'] ?? null,
                ),
            ));
        }

        $sessionMiddlewareConfiguration = $configuration['middleware']['session'] ?? [];
        if (false !== $sessionMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\SessionMiddleware::class,
                new Factory\Middleware\SessionMiddlewareFactory(
                    initializer: $sessionMiddlewareConfiguration['initializer'] ?? null,
                    persistence: $sessionMiddlewareConfiguration['persistence'] ?? null,
                    priority: $sessionMiddlewareConfiguration['priority'] ?? null,
                ),
            ));
        }

        $compressionMiddlewareConfiguration = $configuration['middleware']['compression'] ?? [];
        if (false !== $compressionMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\CompressionMiddleware::class,
                new Factory\Middleware\CompressionMiddlewareFactory(
                    logger: $compressionMiddlewareConfiguration['logger'] ?? null,
                    minimumCompressibleContentLength: $compressionMiddlewareConfiguration['minimum-compressible-content-length'] ?? null,
                    compressibleContentTypesRegex: $compressionMiddlewareConfiguration['compressible-content-types-regex'] ?? null,
                    level: $compressionMiddlewareConfiguration['level'] ?? null,
                    memory: $compressionMiddlewareConfiguration['memory'] ?? null,
                    window: $compressionMiddlewareConfiguration['window'] ?? null,
                    priority: $compressionMiddlewareConfiguration['priority'] ?? null,
                ),
            ));
        }

        $staticContentMiddlewareConfiguration = $configuration['middleware']['static-content'] ?? [];
        if (false !== $staticContentMiddlewareConfiguration) {
            $container->addDefinition(Definition::ofType(
                Middleware\StaticContentMiddleware::class,
                new Factory\Middleware\StaticContentMiddlewareFactory(
                    deliverer: $staticContentMiddlewareConfiguration['deliverer'] ?? null,
                    roots: $staticContentMiddlewareConfiguration['roots'] ?? null,
                    extensions: $staticContentMiddlewareConfiguration['extensions'] ?? null,
                    logger: $staticContentMiddlewareConfiguration['logger'] ?? null,
                    priority: $staticContentMiddlewareConfiguration['priority'] ?? null,
                ),
            ));
        }

        // If no logger is defined, define a null logger to prevent errors.
        if (!$container->hasDefinition(LoggerInterface::class)) {
            $definition = Definition::ofType(NullLogger::class);
            $definition->addAlias(LoggerInterface::class);

            $container->addDefinition($definition);
        }
    }

    /**
     * @inheritDoc
     */
    public function getExtensions(): array
    {
        return [
            new AdvisoryExtension(),
            new EventDispatcherExtension(),
            new CacheExtension(),
            new CsrfExtension(),
            new ConsoleExtension(),
            new HttpExtension(),
        ];
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'commands' => Type\optional(Type\shape([
                'advisory' => Type\optional(Type\shape([
                    'advice' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                        'advisory' => Type\optional(Type\non_empty_string()),
                    ]))),
                ])),
                'http' => Type\optional(Type\shape([
                    'server' => Type\optional(Type\shape([
                        'start' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([]))),
                        'cluster' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                            'watch' => Type\optional(Type\shape([
                                'interval' => Type\optional(Type\float()),
                                'directories' => Type\optional(Type\vec(Type\non_empty_string())),
                                'extensions' => Type\optional(Type\vec(Type\non_empty_string())),
                            ])),
                        ]))),
                    ])),
                ])),
            ])),
            'listeners' => Type\optional(Type\shape([
                'advisory' => Type\optional(Type\shape([
                    'before-execute' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                        'advisory' => Type\optional(Type\non_empty_string()),
                    ]))),
                ])),
                'broadcast' => Type\optional(Type\shape([
                    'server-stopping' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                        'hub-manager' => Type\optional(Type\non_empty_string()),
                    ]))),
                ])),
                'cache' => Type\optional(Type\shape([
                    'server-stopping' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                        'store-manager' => Type\optional(Type\non_empty_string()),
                    ]))),
                ])),
                'database' => Type\optional(Type\shape([
                    'server-stopping' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                        'database-manager' => Type\optional(Type\non_empty_string()),
                    ]))),
                ])),
            ])),
            'middleware' => Type\optional(Type\shape([
                'x-powered-by' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'powered-by' => Type\optional(Type\non_empty_string()),
                    'expose-php-version' => Type\optional(Type\bool()),
                ]))),
                'access-log' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'logger' => Type\optional(Type\non_empty_string()),
                    'priority' => Type\optional(Type\int()),
                ]))),
                'router' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'matcher' => Type\optional(Type\non_empty_string()),
                    'priority' => Type\optional(Type\int()),
                ]))),
                'session' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'initializer' => Type\optional(Type\non_empty_string()),
                    'persistence' => Type\optional(Type\non_empty_string()),
                    'priority' => Type\optional(Type\int()),
                ]))),
                'compression' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'logger' => Type\optional(Type\non_empty_string()),
                    'minimum-compressible-content-length' => Type\optional(Type\positive_int()),
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
                'static-content' => Type\optional(Type\union(Type\literal_scalar(false), Type\shape([
                    'deliverer' => Type\optional(Type\non_empty_string()),
                    'roots' => Type\optional(Type\dict(Type\non_empty_string(), Type\non_empty_string())),
                    'extensions' => Type\optional(Type\vec(Type\non_empty_string())),
                    'logger' => Type\optional(Type\non_empty_string()),
                    'priority' => Type\optional(Type\int()),
                ]))),
            ])),
        ]);
    }
}
