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

namespace Neu\Component\Http\Server\DependencyInjection;

use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\Http\Server\Cluster;
use Neu\Component\Http\Server\ClusterInterface;
use Neu\Component\Http\Server\ClusterWorker;
use Neu\Component\Http\Server\ClusterWorkerInterface;
use Neu\Component\Http\Server\Command\ClusterCommand;
use Neu\Component\Http\Server\Command\StartCommand;
use Neu\Component\Http\Server\DependencyInjection\Factory\ClusterFactory;
use Neu\Component\Http\Server\DependencyInjection\Factory\ClusterWorkerFactory;
use Neu\Component\Http\Server\DependencyInjection\Factory\Command\ClusterCommandFactory;
use Neu\Component\Http\Server\DependencyInjection\Factory\Command\StartCommandFactory;
use Neu\Component\Http\Server\DependencyInjection\Factory\ServerFactory;
use Neu\Component\Http\Server\DependencyInjection\Factory\ServerInfrastructureFactory;
use Neu\Component\Http\Server\Server;
use Neu\Component\Http\Server\ServerInfrastructure;
use Neu\Component\Http\Server\ServerInterface;
use Psl\Type;

/**
 * A dependency injection extension for the HTTP server component.
 *
 * @psalm-import-type ServerSocketConfiguration from ServerInfrastructure
 *
 * @psalm-type Configuration = array{
 *     connection-limit?: positive-int,
 *     connection-limit-per-ip?: positive-int,
 *     stream-timeout?: int,
 *     connection-timeout?: int,
 *     header-size-limit?: int,
 *     body-size-limit?: int,
 *     tls-handshake-timeout?: int,
 *     logger?: non-empty-string,
 *     runtime?: non-empty-string,
 *     event-dispatcher?: non-empty-string,
 *     sockets?: list<ServerSocketConfiguration>,
 *     cluster?: array{
 *         workers?: positive-int,
 *         logger?: non-empty-string,
 *     },
 *     command?: array{
 *          cluster?: array{
 *              watch?: array{
 *                  interval?: float,
 *                  directories?: list<non-empty-string>,
 *                  extensions?: list<non-empty-string>,
 *              },
 *          },
 *     },
 * }
 */
final readonly class ServerExtension implements ExtensionInterface
{
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
            ->getOfTypeOrDefault('server', $this->getConfigurationType(), [])
        ;

        $container->addDefinition(Definition::ofType(ServerInfrastructure::class, new ServerInfrastructureFactory(
            serverSocketConfigurations: $configuration['sockets'] ?? null,
            connectionLimit: $configuration['connection-limit'] ?? null,
            connectionLimitPerIP: $configuration['connection-limit-per-ip'] ?? null,
            streamTimeout: $configuration['stream-timeout'] ?? null,
            connectionTimeout: $configuration['connection-timeout'] ?? null,
            headerSizeLimit: $configuration['header-size-limit'] ?? null,
            bodySizeLimit: $configuration['body-size-limit'] ?? null,
            tlsHandshakeTimeout: $configuration['tls-handshake-timeout'] ?? null,
            logger: $configuration['logger'] ?? $defaultLogger ?? null,
        )));

        $container->addDefinition(Definition::ofType(Server::class, new ServerFactory(
            runtime: $configuration['runtime'] ?? null,
            eventDispatcher: $configuration['event-dispatcher'] ?? null,
            logger: $configuration['logger'] ?? $defaultLogger ?? null,
        )));

        $container->addDefinition(Definition::ofType(ClusterWorker::class, new ClusterWorkerFactory(
            dispatcher: $configuration['event-dispatcher'] ?? null,
            logger: $configuration['cluster']['logger'] ?? $defaultLogger ?? null,
        )));

        $container->addDefinition(Definition::ofType(Cluster::class, new ClusterFactory(
            logger: $configuration['cluster']['logger'] ?? $defaultLogger ?? null,
            eventDispatcher: $configuration['event-dispatcher'] ?? null,
            workers: $configuration['cluster']['workers'] ?? null,
        )));

        $container->getDefinition(Server::class)->addAlias(ServerInterface::class);
        $container->getDefinition(Cluster::class)->addAlias(ClusterInterface::class);
        $container->getDefinition(ClusterWorker::class)->addAlias(ClusterWorkerInterface::class);

        $container->addDefinition(Definition::ofType(StartCommand::class, new StartCommandFactory()));
        $container->addDefinition(Definition::ofType(ClusterCommand::class, new ClusterCommandFactory(
            watchInterval: $configuration['command']['cluster']['watch']['interval'] ?? null,
            watchDirectories: $configuration['command']['cluster']['watch']['directories'] ?? null,
            watchExtensions: $configuration['command']['cluster']['watch']['extensions'] ?? null,
        )));
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'connection-limit' => Type\optional(Type\positive_int()),
            'connection-limit-per-ip' => Type\optional(Type\positive_int()),
            'stream-timeout' => Type\optional(Type\int()),
            'connection-timeout' => Type\optional(Type\int()),
            'header-size-limit' => Type\optional(Type\int()),
            'body-size-limit' => Type\optional(Type\int()),
            'tls-handshake-timeout' => Type\optional(Type\int()),
            'logger' => Type\optional(Type\non_empty_string()),
            'runtime' => Type\optional(Type\non_empty_string()),
            'event-dispatcher' => Type\optional(Type\non_empty_string()),
            'sockets' => Type\vec(Type\shape([
                'host' => Type\non_empty_string(),
                'port' => Type\int(),
                'bind' => Type\optional(Type\shape([
                    'tcp-no-delay' => Type\optional(Type\bool()),
                    'reuse-port' => Type\optional(Type\bool()),
                    'broadcast' => Type\optional(Type\bool()),
                    'tls' => Type\optional(Type\shape([
                        'minimum-version' => Type\optional(Type\int()),
                        'verify-peer' => Type\optional(Type\bool()),
                        'capture-peer' => Type\optional(Type\bool()),
                        'verify-depth' => Type\optional(Type\int()),
                        'security-level' => Type\optional(Type\union(
                            Type\literal_scalar(0),
                            Type\literal_scalar(1),
                            Type\literal_scalar(2),
                            Type\literal_scalar(3),
                            Type\literal_scalar(4),
                            Type\literal_scalar(5),
                        )),
                        'peer-name' => Type\optional(Type\non_empty_string()),
                        'ciphers' => Type\optional(Type\non_empty_string()),
                        'alpn-protocols' => Type\optional(Type\vec(Type\non_empty_string())),
                        'certificate-authority' => Type\optional(Type\shape([
                            'file' => Type\optional(Type\non_empty_string()),
                            'path' => Type\optional(Type\non_empty_string()),
                        ])),
                        'certificate' => Type\optional(Type\shape([
                            'file' => Type\non_empty_string(),
                            'key' => Type\non_empty_string(),
                            'passphrase' => Type\optional(Type\non_empty_string()),
                        ])),
                        'certificates' => Type\optional(Type\dict(
                            Type\string(),
                            Type\shape([
                                'file' => Type\non_empty_string(),
                                'key' => Type\non_empty_string(),
                                'passphrase' => Type\optional(Type\non_empty_string()),
                            ])
                        )),
                    ])),
                ])),
            ])),
            'cluster' => Type\optional(Type\shape([
                'workers' => Type\optional(Type\positive_int()),
                'logger' => Type\optional(Type\non_empty_string()),
            ])),
            'command' => Type\optional(Type\shape([
                'cluster' => Type\optional(Type\shape([
                    'watch' => Type\optional(Type\shape([
                        'interval' => Type\optional(Type\float()),
                        'directories' => Type\optional(Type\vec(Type\non_empty_string())),
                        'extensions' => Type\optional(Type\vec(Type\non_empty_string())),
                    ])),
                ])),
            ])),
        ]);
    }
}
