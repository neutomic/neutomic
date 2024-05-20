<?php

declare(strict_types=1);

namespace Neu\Component\Database\DependencyInjection;

use Amp\Mysql\MysqlConnection;
use Amp\Mysql\MysqlConnectionPool;
use Amp\Postgres\PostgresConnection;
use Amp\Postgres\PostgresConnectionPool;
use Neu\Component\Configuration\Exception\InvalidConfigurationException;
use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\DatabaseManager;
use Neu\Component\Database\DatabaseManagerInterface;
use Neu\Component\Database\DependencyInjection\Factory\DatabaseFactory;
use Neu\Component\Database\DependencyInjection\Factory\DatabaseManagerFactory;
use Neu\Component\Database\DependencyInjection\Factory\MysqlConnectionFactory;
use Neu\Component\Database\DependencyInjection\Factory\MysqlConnectionPoolFactory;
use Neu\Component\Database\DependencyInjection\Factory\PostgresConnectionFactory;
use Neu\Component\Database\DependencyInjection\Factory\PostgresConnectionPoolFactory;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Psl\Class;
use Psl\Type;

/**
 * @psalm-type MysqlConnectionConfiguration = array{
 *     platform: 'mysql'|'mysqli'|'mariadb',
 *     host: non-empty-string,
 *     port?: int,
 *     user?: string,
 *     password?: string,
 *     database?: string,
 *     charset?: string,
 *     collate?: string,
 *     sql-mode?: string,
 *     use-compression?: bool,
 *     key?: string,
 *     use-local-infile?: bool,
 *     pooled?: bool,
 *     max-connections?: int,
 *     idle-timeout?: int,
 *     reset-connections?: bool
 * }
 * @psalm-type PostgresConnectionConfiguration = array{
 *     platform: 'pgsql'|'postgres'|'postgresql',
 *     host: non-empty-string,
 *     port?: int,
 *     user?: string,
 *     password?: string,
 *     database?: string,
 *     application-name?: string,
 *     ssl-mode?: string,
 *     pooled?: bool,
 *     max-connections?: int,
 *     idle-timeout?: int,
 *     reset-connections?: bool
 * }
 * @psalm-type DatabaseConfiguration = MysqlConnectionConfiguration|PostgresConnectionConfiguration
 * @psalm-type Configuration = array{
 *     default?: non-empty-string,
 *     databases: non-empty-array<non-empty-string, DatabaseConfiguration>
 * }
 */
final class DatabaseExtension implements ExtensionInterface
{
    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'databases' => Type\non_empty_dict(
                Type\non_empty_string(),
                Type\union(
                    Type\shape([
                        'platform' => Type\union(
                            Type\literal_scalar('mysql'),
                            Type\literal_scalar('mysqli'),
                            Type\literal_scalar('mariadb')
                        ),
                        'host' => Type\non_empty_string(),
                        'port' => Type\optional(Type\int()),
                        'user' => Type\optional(Type\string()),
                        'password' => Type\optional(Type\string()),
                        'database' => Type\optional(Type\string()),
                        'charset' => Type\optional(Type\string()),
                        'collate' => Type\optional(Type\string()),
                        'sql-mode' => Type\optional(Type\string()),
                        'use-compression' => Type\optional(Type\bool()),
                        'key' => Type\optional(Type\string()),
                        'use-local-infile' => Type\optional(Type\bool()),
                        'pooled' => Type\optional(Type\bool()),
                        'max-connections' => Type\optional(Type\int()),
                        'idle-timeout' => Type\optional(Type\int()),
                        'reset-connections' => Type\optional(Type\bool()),
                    ]),
                    Type\shape([
                        'platform' => Type\union(
                            Type\literal_scalar('pgsql'),
                            Type\literal_scalar('postgres'),
                            Type\literal_scalar('postgresql')
                        ),
                        'host' => Type\non_empty_string(),
                        'port' => Type\optional(Type\int()),
                        'user' => Type\optional(Type\string()),
                        'password' => Type\optional(Type\string()),
                        'database' => Type\optional(Type\string()),
                        'application-name' => Type\optional(Type\string()),
                        'ssl-mode' => Type\optional(Type\string()),
                        'pooled' => Type\optional(Type\bool()),
                        'max-connections' => Type\optional(Type\int()),
                        'idle-timeout' => Type\optional(Type\int()),
                        'reset-connections' => Type\optional(Type\bool()),
                    ])
                ),
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register(ContainerBuilderInterface $container): void
    {
        if (!$container->getConfiguration()->has('database')) {
            throw new InvalidConfigurationException('The database extension requires a "database" configuration section.');
        }

        /** @var Configuration $configuration */
        $configuration = $container
            ->getConfiguration()
            ->getOfType('database', $this->getConfigurationType())
        ;

        $databases = $configuration['databases'];

        $databaseDefinitions = $this->registerDatabases($container, $databases);
        $defaultDatabase = $configuration['default'] ?? array_key_first($databaseDefinitions);

        $this->registerDatabaseManager($container, $defaultDatabase, $databaseDefinitions);
    }

    /**
     * Register databases.
     *
     * @param ContainerBuilderInterface $container
     * @param array<non-empty-string, DatabaseConfiguration> $databases
     *
     * @return array<non-empty-string, non-empty-string> Map of database names to database service IDs
     */
    private function registerDatabases(ContainerBuilderInterface $container, array $databases): array
    {
        $databaseDefinitions = [];

        foreach ($databases as $name => $config) {
            $connectionServiceId = 'database.connection.' . $name;
            $databaseServiceId = 'database.' . $name;

            if ($config['platform'] === 'mysql' || $config['platform'] === 'mysqli' || $config['platform'] === 'mariadb') {
                /** @var MysqlConnectionConfiguration $config */
                $this->registerMysqlConnection($container, $connectionServiceId, $config);
            } elseif ($config['platform'] === 'pgsql' || $config['platform'] === 'postgres' || $config['platform'] === 'postgresql') {
                /** @var PostgresConnectionConfiguration $config */
                $this->registerPostgresConnection($container, $connectionServiceId, $config);
            } else {
                throw new InvalidConfigurationException('Unknown platform: ' . $config['platform']);
            }

            $container->addDefinition(Definition::create($databaseServiceId, DatabaseInterface::class, new DatabaseFactory(
                connection: $connectionServiceId,
            )));

            $databaseDefinitions[$name] = $databaseServiceId;
        }

        return $databaseDefinitions;
    }

    /**
     * Register a MySQL connection.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param MysqlConnectionConfiguration $config
     */
    private function registerMysqlConnection(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        if (!Class\exists(MysqlConnection::class)) {
            throw new InvalidConfigurationException('The "amphp/mysql" package is required to use the mysql database connection.');
        }

        $pooled = $config['pooled'] ?? true;
        if ($pooled) {
            $container->addDefinition(Definition::create($serviceId, MysqlConnectionPool::class, new MysqlConnectionPoolFactory(
                host: $config['host'],
                port: $config['port'] ?? null,
                user: $config['user'] ?? null,
                password: $config['password'] ?? null,
                database: $config['database'] ?? null,
                charset: $config['charset'] ?? null,
                collate: $config['collate'] ?? null,
                sqlMode: $config['sql-mode'] ?? null,
                useCompression: $config['use-compression'] ?? null,
                key: $config['key'] ?? null,
                useLocalInfile: $config['use-local-infile'] ?? null,
                maxConnections: $config['max-connections'] ?? null,
                idleTimeout: $config['idle-timeout'] ?? null,
                resetConnections: $config['reset-connections'] ?? null,
            )));

            return;
        }

        $container->addDefinition(Definition::create($serviceId, MysqlConnection::class, new MysqlConnectionFactory(
            host: $config['host'],
            port: $config['port'] ?? null,
            user: $config['user'] ?? null,
            password: $config['password'] ?? null,
            database: $config['database'] ?? null,
            charset: $config['charset'] ?? null,
            collate: $config['collate'] ?? null,
            sqlMode: $config['sql-mode'] ?? null,
            useCompression: $config['use-compression'] ?? null,
            key: $config['key'] ?? null,
            useLocalInfile: $config['use-local-infile'] ?? null,
        )));
    }

    /**
     * Register a Postgres connection.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $serviceId
     * @param PostgresConnectionConfiguration $config
     */
    private function registerPostgresConnection(ContainerBuilderInterface $container, string $serviceId, array $config): void
    {
        if (!Class\exists(PostgresConnection::class)) {
            throw new InvalidConfigurationException('The "amphp/postgres" package is required to use the postgres database connection.');
        }

        $pooled = $config['pooled'] ?? true;
        if ($pooled) {
            $container->addDefinition(Definition::create($serviceId, PostgresConnectionPool::class, new PostgresConnectionPoolFactory(
                host: $config['host'],
                port: $config['port'] ?? null,
                user: $config['user'] ?? null,
                password: $config['password'] ?? null,
                database: $config['database'] ?? null,
                applicationName: $config['application-name'] ?? null,
                sslMode: $config['ssl-mode'] ?? null,
                maxConnections: $config['max-connections'] ?? null,
                idleTimeout: $config['idle-timeout'] ?? null,
                resetConnections: $config['reset-connections'] ?? null,
            )));

            return;
        }

        $container->addDefinition(Definition::create($serviceId, PostgresConnection::class, new PostgresConnectionFactory(
            host: $config['host'],
            port: $config['port'] ?? null,
            user: $config['user'] ?? null,
            password: $config['password'] ?? null,
            database: $config['database'] ?? null,
            applicationName: $config['application-name'] ?? null,
            sslMode: $config['ssl-mode'] ?? null,
        )));
    }

    /**
     * Register the {@see DatabaseManager} service.
     *
     * @param ContainerBuilderInterface $container
     * @param non-empty-string $defaultDatabase
     * @param array<non-empty-string, non-empty-string> $databaseDefinitions
     */
    private function registerDatabaseManager(ContainerBuilderInterface $container, string $defaultDatabase, array $databaseDefinitions): void
    {
        $container->addDefinition(Definition::ofType(DatabaseManager::class, new DatabaseManagerFactory(
            defaultDatabaseId: $defaultDatabase,
            services: $databaseDefinitions,
        )));

        $container->getDefinition(DatabaseManager::class)->addAlias(DatabaseManagerInterface::class);
    }
}
