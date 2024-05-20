<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection;

use Amp\File\Filesystem;
use Amp\Redis\RedisConfig;
use Neu\Component\Cache\DependencyInjection\Factory\Driver\FilesystemDriverFactory;
use Neu\Component\Cache\DependencyInjection\Factory\Driver\LocalDriverFactory;
use Neu\Component\Cache\DependencyInjection\Factory\Driver\RedisDriverFactory;
use Neu\Component\Cache\DependencyInjection\Factory\StoreFactory;
use Neu\Component\Cache\DependencyInjection\Factory\StoreManagerFactory;
use Neu\Component\Cache\Driver\DriverInterface;
use Neu\Component\Cache\Driver\FilesystemDriver;
use Neu\Component\Cache\Driver\LocalDriver;
use Neu\Component\Cache\Driver\RedisDriver;
use Neu\Component\Cache\StoreInterface;
use Neu\Component\Cache\StoreManager;
use Neu\Component\Cache\StoreManagerInterface;
use Neu\Component\Configuration\Exception\InvalidConfigurationException;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Class;
use Psl\Type;

use function array_key_first;

/**
 * @psalm-type FilesystemDriverConfiguration = array{
 *      driver: 'filesystem',
 *      directory: non-empty-string,
 *      prune-interval?: int
 * }
 * @psalm-type LocalDriverConfiguration = array{
 *      driver: 'local',
 *      size?: int,
 *      prune-interval?: int
 * }
 * @psalm-type RedisDriverConfiguration = array{
 *      driver: 'redis'|'valkey',
 *      uri: non-empty-string,
 *      timeout?: int,
 *      database?: int,
 *      password?: string
 * }
 * @psalm-type ServiceDriverConfiguration = array{
 *      driver: 'service',
 *      service: non-empty-string
 * }
 * @psalm-type DriverConfiguration = LocalDriverConfiguration|FilesystemDriverConfiguration|RedisDriverConfiguration|ServiceDriverConfiguration
 * @psalm-type Configuration = array{
 *      default?: non-empty-string,
 *      stores?: non-empty-array<non-empty-string, DriverConfiguration>
 * }
 */
final class CacheExtension implements ExtensionInterface
{
    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'stores' => Type\optional(Type\dict(
                Type\non_empty_string(),
                Type\union(
                    Type\shape([
                        'driver' => Type\literal_scalar('local'),
                        'size' => Type\optional(Type\int()),
                        'prune-interval' => Type\optional(Type\int()),
                    ]),
                    Type\shape([
                        'driver' => Type\literal_scalar('filesystem'),
                        'directory' => Type\non_empty_string(),
                        'prune-interval' => Type\optional(Type\int()),
                    ]),
                    Type\shape([
                        'driver' => Type\union(Type\literal_scalar('redis'), Type\literal_scalar('valkey')),
                        'uri' => Type\non_empty_string(),
                        'timeout' => Type\optional(Type\int()),
                        'database' => Type\optional(Type\int()),
                        'password' => Type\optional(Type\string()),
                    ]),
                    Type\shape([
                        'driver' => Type\literal_scalar('service'),
                        'service' => Type\non_empty_string(),
                    ])
                ),
            )),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getOfTypeOrDefault('cache', $this->getConfigurationType(), [])
        ;

        $stores = $configuration['stores'] ?? [];

        // If no stores are defined, default to a single local store
        if (empty($stores)) {
            $stores = ['default' => ['driver' => 'local']];
        }

        $storeDefinitions = $this->registerStores($container, $stores);
        $defaultStore = $configuration['default'] ?? array_key_first($storeDefinitions);

        $this->setDefaultStore($container, $storeDefinitions, $defaultStore);

        $this->registerStoreManager($container, $defaultStore, $storeDefinitions);
    }

    /**
     * Register cache stores.
     *
     * @param ContainerBuilderInterface $container
     * @param array<non-empty-string, DriverConfiguration> $stores
     *
     * @return array<non-empty-string, non-empty-string> Map of store names to store service IDs
     */
    private function registerStores(ContainerBuilderInterface $container, array $stores): array
    {
        $storeDefinitions = [];

        foreach ($stores as $name => $config) {
            $driverServiceId = 'cache.driver.' . $name;
            $storeServiceId = 'cache.' . $name;

            match ($config['driver']) {
                'local' => $this->registerLocalDriver($container, $driverServiceId, $config),
                'filesystem' => $this->registerFilesystemDriver($container, $driverServiceId, $config),
                'redis', 'valkey' => $this->registerRedisDriver($container, $driverServiceId, $config),
                'service' => $this->registerServiceDriver($container, $driverServiceId, $config),
                default => throw new InvalidConfigurationException('Unknown driver: ' . $config['driver']),
            };

            $container->addDefinition(Definition::create($storeServiceId, StoreInterface::class, new StoreFactory($driverServiceId)));

            $storeDefinitions[$name] = $storeServiceId;
        }

        return $storeDefinitions;
    }

    /**
     * Register a local cache driver.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param LocalDriverConfiguration $config
     */
    private function registerLocalDriver(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        $container->addDefinition(Definition::create($serviceId, LocalDriver::class, new LocalDriverFactory(
            pruneInterval: $config['prune-interval'] ?? null,
            size: $config['size'] ?? null,
        )));
    }

    /**
     * Register a filesystem cache driver.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param FilesystemDriverConfiguration $config
     */
    private function registerFilesystemDriver(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        $container->addDefinition(Definition::create($serviceId, FilesystemDriver::class, new FilesystemDriverFactory(
            directory: $config['directory'],
            pruneInterval: $config['prune-interval'] ?? null,
        )));
    }

    /**
     * Register a redis cache driver.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param RedisDriverConfiguration $config
     */
    private function registerRedisDriver(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        if (!Class\exists(RedisConfig::class)) {
            throw new InvalidConfigurationException('The "amphp/redis" package is required to use the redis cache driver.');
        }

        $container->addDefinition(Definition::create($serviceId, RedisDriver::class, new RedisDriverFactory(
            uri: $config['uri'],
            timeout: $config['timeout'] ?? null,
            database: $config['database'] ?? null,
            password: $config['password'] ?? null,
        )));
    }

    /**
     * Register a custom service driver.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param ServiceDriverConfiguration $config
     */
    private function registerServiceDriver(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        $serviceDefinition = $container->getDefinition($config['service']);

        if (!$serviceDefinition->isInstanceOf(DriverInterface::class)) {
            throw new InvalidConfigurationException('The service "' . $config['service'] . '" must implement "' . DriverInterface::class . '".');
        }

        $serviceDefinition->addAlias($serviceId);
    }

    /**
     * Set the default cache store.
     *
     * @param ContainerBuilderInterface $container
     * @param array<non-empty-string, non-empty-string> $storeDefinitions
     * @param non-empty-string $defaultStore
     */
    private function setDefaultStore(ContainerBuilderInterface $container, array $storeDefinitions, string $defaultStore): void
    {
        if (!isset($storeDefinitions[$defaultStore])) {
            if (!$container->hasDefinition($defaultStore)) {
                throw new InvalidConfigurationException('The default cache store "' . $defaultStore . '" is not defined.');
            }

            $definition = $container->getDefinition($defaultStore);
        } else {
            $definition = $container->getDefinition($storeDefinitions[$defaultStore]);
        }

        if (!$definition->isInstanceOf(StoreInterface::class)) {
            throw new InvalidConfigurationException('The default cache store "' . $defaultStore . '" must be an instance of "' . StoreInterface::class . '".');
        }

        $definition->addAlias(StoreInterface::class);
    }

    /**
     * Register the {@see StoreManager} service.
     *
     * @param ContainerBuilderInterface $container
     * @param string $defaultStore
     * @param array<string, string> $storeDefinitions
     */
    private function registerStoreManager(ContainerBuilderInterface $container, string $defaultStore, array $storeDefinitions): void
    {
        $container->addDefinition(
            Definition::ofType(StoreManager::class, new StoreManagerFactory($defaultStore, $storeDefinitions)),
        );

        $container->getDefinition(StoreManager::class)->addAlias(StoreManagerInterface::class);
    }
}
