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

namespace Neu\Component\Broadcast\DependencyInjection;

use Amp\Postgres\PostgresConfig;
use Neu\Component\Broadcast\DependencyInjection\Factory\HubFactory;
use Neu\Component\Broadcast\DependencyInjection\Factory\HubManagerFactory;
use Neu\Component\Broadcast\DependencyInjection\Factory\Transport\LocalTransportFactory;
use Neu\Component\Broadcast\DependencyInjection\Factory\Transport\MemoryTransportFactory;
use Neu\Component\Broadcast\DependencyInjection\Factory\Transport\PostgresTransportFactory;
use Neu\Component\Broadcast\HubInterface;
use Neu\Component\Broadcast\HubManager;
use Neu\Component\Broadcast\HubManagerInterface;
use Neu\Component\Broadcast\Transport\LocalTransport;
use Neu\Component\Broadcast\Transport\MemoryTransport;
use Neu\Component\Broadcast\Transport\PostgresTransport;
use Neu\Component\Broadcast\Transport\TransportInterface;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\Exception\InvalidConfigurationException;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Psl\Class;
use Psl\Type;
use Override;

use function array_key_first;

/**
 * A dependency injection extension for the broadcast component.
 *
 * @psalm-type PostgresSslMode = 'disable'|'allow'|'prefer'|'require'|'verify-ca'|'verify-full'
 * @psalm-type LocalTransportConfiguration = array{
 *     transport: 'local',
 * }
 * @psalm-type MemoryTransportConfiguration = array{
 *     transport: 'memory'
 * }
 * @psalm-type PostgresTransportConfiguration = array{
 *      transport: 'pgsql'|'postgres'|'postgresql',
 *      host: non-empty-string,
 *      port?: int,
 *      user?: string,
 *      username?: string,
 *      password?: string,
 *      database?: string,
 *      application-name?: string,
 *      ssl-mode?: PostgresSslMode,
 *  }
 * @psalm-type ServiceTransportConfiguration = array{
 *      transport: 'service',
 *      service: non-empty-string
 * }
 * @psalm-type TransportConfiguration = LocalTransportConfiguration|MemoryTransportConfiguration|PostgresTransportConfiguration|ServiceTransportConfiguration
 * @psalm-type Configuration = array{
 *      default?: non-empty-string,
 *      hubs?: array<non-empty-string, TransportConfiguration>
 * }
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class BroadcastExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations
            ->getOfTypeOrDefault('broadcast', $this->getConfigurationType(), [])
        ;

        $hubs = $configuration['hubs'] ?? [];

        // If no hubs are defined, default to a single local hub.
        if (empty($hubs)) {
            $hubs = ['default' => ['transport' => 'local']];
        }

        $hubServices = $this->registerHubs($registry, $hubs);
        $defaultHub = $configuration['default'] ?? array_key_first($hubServices);

        $this->setDefaultHub($registry, $hubServices, $defaultHub);

        $this->registerHubManager($registry, $defaultHub, $hubServices);
    }

    /**
     * Register broadcast hubs.
     *
     * @param RegistryInterface $registry
     * @param non-empty-array<non-empty-string, TransportConfiguration> $hubs
     *
     * @return non-empty-array<non-empty-string, non-empty-string> Map of hub names to hub service IDs
     */
    private function registerHubs(RegistryInterface $registry, array $hubs): array
    {
        $hubServices = [];

        $registeredLocalTransport = false;
        foreach ($hubs as $name => $config) {
            $transportServiceId = 'broadcast.transport.' . $name;
            $hubServiceId = 'broadcast.hub.' . $name;

            $transport = $config['transport'];
            if ('local' === $transport) {
                if ($registeredLocalTransport) {
                    throw new RuntimeException('Only one local broadcast transport can be registered.');
                }

                $registeredLocalTransport = true;
                $this->registerLocalTransport($registry, $transportServiceId);
            } elseif ('memory' === $transport) {
                $this->registerMemoryTransport($registry, $transportServiceId);
            } elseif ('pgsql' === $transport || 'postgres' === $transport || 'postgresql' === $transport) {
                /** @var PostgresTransportConfiguration $config */
                $this->registerPostgresTransport($registry, $transportServiceId, $config);
            } else {
                /** @var ServiceTransportConfiguration $config */
                $this->registerServiceTransport($registry, $transportServiceId, $config);
            }

            $registry->addDefinition(Definition::create($hubServiceId, HubInterface::class, new HubFactory($transportServiceId)));

            $hubServices[$name] = $hubServiceId;
        }

        return $hubServices;
    }

    /**
     * Register a memory transport.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     */
    private function registerMemoryTransport(RegistryInterface $registry, string $serviceId): void
    {
        $registry->addDefinition(Definition::create($serviceId, MemoryTransport::class, new MemoryTransportFactory()));
    }

    /**
     * Register a local broadcast transport.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     */
    private function registerLocalTransport(RegistryInterface $registry, string $serviceId): void
    {
        $registry->addDefinition(Definition::create($serviceId, LocalTransport::class, new LocalTransportFactory()));
    }

    /**
     * Register a postgres broadcast transport.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     * @param PostgresTransportConfiguration $config
     */
    private function registerPostgresTransport(RegistryInterface $registry, string $serviceId, array $config): void
    {
        if (!Class\exists(PostgresConfig::class)) {
            throw new InvalidConfigurationException('The "amphp/postgres" package is required to use the postgres broadcast transport.');
        }

        $registry->addDefinition(Definition::create($serviceId, PostgresTransport::class, new PostgresTransportFactory(
            host: $config['host'],
            port: $config['port'] ?? null,
            user: $config['user'] ?? $config['username'] ?? null,
            password: $config['password'] ?? null,
            database: $config['database'] ?? null,
            applicationName: $config['application-name'] ?? null,
            sslMode: $config['ssl-mode'] ?? null,
        )));
    }

    /**
     * Register a custom service transport.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     * @param ServiceTransportConfiguration $config
     */
    private function registerServiceTransport(RegistryInterface $registry, string $serviceId, array $config): void
    {
        $serviceDefinition = $registry->getDefinition($config['service']);

        if (!$serviceDefinition->isInstanceOf(TransportInterface::class)) {
            throw new InvalidConfigurationException('The service "' . $config['service'] . '" must implement "' . TransportInterface::class . '".');
        }

        $serviceDefinition->addAlias($serviceId);
    }

    /**
     * Set the default broadcast hub.
     *
     * @param RegistryInterface $registry
     * @param array<non-empty-string, non-empty-string> $hubServices
     * @param non-empty-string $defaultHub
     */
    private function setDefaultHub(RegistryInterface $registry, array $hubServices, string $defaultHub): void
    {
        if (!isset($hubServices[$defaultHub])) {
            if (!$registry->hasDefinition($defaultHub)) {
                throw new InvalidConfigurationException('The default broadcast hub "' . $defaultHub . '" is not defined.');
            }

            $definition = $registry->getDefinition($defaultHub);
        } else {
            $definition = $registry->getDefinition($hubServices[$defaultHub]);
        }

        if (!$definition->isInstanceOf(HubInterface::class)) {
            throw new InvalidConfigurationException('The default broadcast hub "' . $defaultHub . '" must be an instance of "' . HubInterface::class . '".');
        }

        $definition->addAlias(HubInterface::class);
    }

    /**
     * Register the {@see HubManager} service.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $defaultHubId
     * @param array<non-empty-string, non-empty-string> $hubServices
     */
    private function registerHubManager(RegistryInterface $registry, string $defaultHubId, array $hubServices): void
    {
        $definition = Definition::ofType(HubManager::class, new HubManagerFactory($defaultHubId, $hubServices));
        $definition->addAlias(HubManagerInterface::class);

        $registry->addDefinition($definition);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'hubs' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\union(
                    Type\shape([
                        'transport' => Type\literal_scalar('local'),
                    ]),
                    Type\shape([
                        'transport' => Type\literal_scalar('memory'),
                    ]),
                    Type\shape([
                        'transport' => Type\union(
                            Type\literal_scalar('pgsql'),
                            Type\literal_scalar('postgres'),
                            Type\literal_scalar('postgresql')
                        ),
                        'host' => Type\non_empty_string(),
                        'port' => Type\optional(Type\int()),
                        'user' => Type\optional(Type\string()),
                        'username' => Type\optional(Type\string()),
                        'password' => Type\optional(Type\string()),
                        'database' => Type\optional(Type\string()),
                        'application-name' => Type\optional(Type\string()),
                        'ssl-mode' => Type\optional(Type\union(
                            Type\literal_scalar('disable'),
                            Type\literal_scalar('allow'),
                            Type\literal_scalar('prefer'),
                            Type\literal_scalar('require'),
                            Type\literal_scalar('verify-ca'),
                            Type\literal_scalar('verify-full')
                        )),
                    ]),
                    Type\shape([
                        'transport' => Type\literal_scalar('service'),
                        'service' => Type\non_empty_string(),
                    ])
                ),
            )),
        ]);
    }
}
