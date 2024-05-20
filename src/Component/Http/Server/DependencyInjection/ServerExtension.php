<?php

declare(strict_types=1);

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
 * @psalm-type ServerSocketTlsBindConfiguration = array{
 *     minimum-version?: int,
 *     verify-peer?: bool,
 *     capture-peer?: bool,
 *     verify-depth?: int,
 *     security-level?: 0|1|2|3|4|5,
 *     peer-name?: non-empty-string,
 *     ciphers?: non-empty-string,
 *     alpn-protocols?: non-empty-list<non-empty-string>,
 *     certificate-authority?: array{
 *         file?: non-empty-string,
 *         path?: non-empty-string,
 *     },
 *     certificate?: array{
 *         file: non-empty-string,
 *         key?: non-empty-string,
 *         passphrase?: non-empty-string,
 *     },
 *     certificates?: non-empty-array<string, array{
 *         file: non-empty-string,
 *         key?: non-empty-string,
 *         passphrase?: non-empty-string,
 *     }>,
 * }
 * @psalm-type ServerSocketBindConfiguration = array{
 *     tcp-no-delay?: bool,
 *     reuse-port?: bool,
 *     broadcast?: bool,
 *     tls?: ServerSocketTlsBindConfiguration,
 * }
 * @psalm-type ServerSocketConfigurationType = array{
 *     host: non-empty-string,
 *     port: int,
 *     bind?: ServerSocketBindConfiguration,
 * }
 * @psalm-type Configuration = array{
 *     connection-limit?: int,
 *     connection-limit-per-ip?: int,
 *     stream-timeout?: int,
 *     connection-timeout?: int,
 *     header-size-limit?: int,
 *     body-size-limit?: int,
 *     tls-handshake-timeout?: int,
 *     logger?: non-empty-string,
 *     runtime?: non-empty-string,
 *     event-dispatcher?: non-empty-string,
 *     sockets?: list<ServerSocketConfigurationType>,
 *     cluster?: array{
 *         workers?: int,
 *         logger?: non-empty-string,
 *     },
 *     command?: array{
 *          cluster?: array{
 *              watch?: array{
 *                  interval?: int,
 *                  directories?: non-empty-list<non-empty-string>,
 *                  extensions?: non-empty-list<non-empty-string>,
 *              },
 *          },
 *     },
 * }
 */
final readonly class ServerExtension implements ExtensionInterface
{
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
            ->getOfTypeOrDefault('server', $this->getConfigurationType(), [])
        ;

        $container->addDefinitions([
            Definition::ofType(ServerInfrastructure::class, new ServerInfrastructureFactory(
                $configuration['sockets'] ?? null,
                $configuration['connection-limit'] ?? null,
                $configuration['connection-limit-per-ip'] ?? null,
                $configuration['stream-timeout'] ?? null,
                $configuration['connection-timeout'] ?? null,
                $configuration['header-size-limit'] ?? null,
                $configuration['body-size-limit'] ?? null,
                $configuration['tls-handshake-timeout'] ?? null,
                $configuration['logger'] ?? $defaultLogger ?? null,
            )),
            Definition::ofType(Server::class, new ServerFactory(
                $configuration['runtime'] ?? null,
                $configuration['event-dispatcher'] ?? null,
                $configuration['logger'] ?? $defaultLogger ?? null,
            )),
            Definition::ofType(ClusterWorker::class, new ClusterWorkerFactory(
                $configuration['event-dispatcher'] ?? null,
                $configuration['cluster']['logger'] ?? $defaultLogger ?? null,
            )),
            Definition::ofType(Cluster::class, new ClusterFactory(
                $configuration['cluster']['logger'] ?? $defaultLogger ?? null,
                $configuration['cluster']['workers'] ?? null,
            )),
        ]);

        $container->getDefinition(Server::class)->addAlias(ServerInterface::class);
        $container->getDefinition(Cluster::class)->addAlias(ClusterInterface::class);
        $container->getDefinition(ClusterWorker::class)->addAlias(ClusterWorkerInterface::class);

        $container->addDefinitions([
            Definition::ofType(StartCommand::class, new StartCommandFactory()),
            Definition::ofType(ClusterCommand::class, new ClusterCommandFactory(
                $configuration['command']['cluster']['watch']['interval'] ?? null,
                $configuration['command']['cluster']['watch']['directories'] ?? null,
                $configuration['command']['cluster']['watch']['extensions'] ?? null,
            ))
        ]);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    private function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'connection-limit' => Type\optional(Type\int()),
            'connection-limit-per-ip' => Type\optional(Type\int()),
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
                'workers' => Type\optional(Type\int()),
                'logger' => Type\optional(Type\non_empty_string()),
            ])),
            'command' => Type\optional(Type\shape([
                'cluster' => Type\optional(Type\shape([
                    'watch' => Type\optional(Type\shape([
                        'interval' => Type\optional(Type\int()),
                        'directories' => Type\optional(Type\vec(Type\non_empty_string())),
                        'extensions' => Type\optional(Type\vec(Type\non_empty_string())),
                    ])),
                ])),
            ])),
        ]);
    }
}
