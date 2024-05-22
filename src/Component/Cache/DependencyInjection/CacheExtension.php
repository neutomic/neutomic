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
 * A dependency injection extension for the cache component.
 *
 * @psalm-type FilesystemDriverConfiguration = array{
 *      driver: 'filesystem',
 *      directory: non-empty-string,
 *      prune-interval?: positive-int,
 * }
 * @psalm-type LocalDriverConfiguration = array{
 *      driver: 'local',
 *      size?: positive-int,
 *      prune-interval?: positive-int,
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
 *      stores?: array<non-empty-string, DriverConfiguration>
 * }
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class CacheExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
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
     * @param non-empty-array<non-empty-string, DriverConfiguration> $stores
     *
     * @return non-empty-array<non-empty-string, non-empty-string> Map of store names to store service IDs
     */
    private function registerStores(ContainerBuilderInterface $container, array $stores): array
    {
        $storeDefinitions = [];

        foreach ($stores as $name => $config) {
            $driverServiceId = 'cache.driver.' . $name;
            $storeServiceId = 'cache.' . $name;

            $driver = $config['driver'];
            if ('local' === $driver) {
                /** @var LocalDriverConfiguration $config */
                $this->registerLocalDriver($container, $driverServiceId, $config);
            } elseif ('filesystem' === $driver) {
                /** @var FilesystemDriverConfiguration $config */
                $this->registerFilesystemDriver($container, $driverServiceId, $config);
            } elseif ('redis' === $driver || 'valkey' === $driver) {
                /** @var RedisDriverConfiguration $config */
                $this->registerRedisDriver($container, $driverServiceId, $config);
            } else {
                /** @var ServiceDriverConfiguration $config */
                $this->registerServiceDriver($container, $driverServiceId, $config);
            }

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
     * @param non-empty-string $defaultStore
     * @param array<non-empty-string, non-empty-string> $storeDefinitions
     */
    private function registerStoreManager(ContainerBuilderInterface $container, string $defaultStore, array $storeDefinitions): void
    {
        $definition = Definition::ofType(StoreManager::class, new StoreManagerFactory($defaultStore, $storeDefinitions));
        $definition->addAlias(StoreManagerInterface::class);

        $container->addDefinition($definition);
    }

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
                        'size' => Type\optional(Type\positive_int()),
                        'prune-interval' => Type\optional(Type\positive_int()),
                    ]),
                    Type\shape([
                        'driver' => Type\literal_scalar('filesystem'),
                        'directory' => Type\non_empty_string(),
                        'prune-interval' => Type\optional(Type\positive_int()),
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
}
