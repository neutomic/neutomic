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

namespace Neu\Component\Database\DependencyInjection;

use Amp\Mysql\MysqlConnection;
use Amp\Mysql\MysqlConnectionPool;
use Amp\Postgres\PostgresConnection;
use Amp\Postgres\PostgresConnectionPool;
use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\DatabaseManager;
use Neu\Component\Database\DatabaseManagerInterface;
use Neu\Component\Database\DependencyInjection\Factory\DatabaseFactory;
use Neu\Component\Database\DependencyInjection\Factory\DatabaseManagerFactory;
use Neu\Component\Database\DependencyInjection\Factory\MysqlConnectionFactory;
use Neu\Component\Database\DependencyInjection\Factory\MysqlConnectionPoolFactory;
use Neu\Component\Database\DependencyInjection\Factory\PostgresConnectionFactory;
use Neu\Component\Database\DependencyInjection\Factory\PostgresConnectionPoolFactory;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Definition\Definition;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\ExtensionInterface;
use Neu\Component\DependencyInjection\RegistryInterface;
use Psl\Class;
use Psl\Type;
use Override;

/**
 * A dependency injection extension for database connections.
 *
 * @psalm-type PostgresSslMode = 'disable'|'allow'|'prefer'|'require'|'verify-ca'|'verify-full'
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
 *     max-connections?: positive-int,
 *     idle-timeout?: positive-int,
 * }
 * @psalm-type PostgresConnectionConfiguration = array{
 *     platform: 'pgsql'|'postgres'|'postgresql',
 *     host: non-empty-string,
 *     port?: int,
 *     user?: string,
 *     password?: string,
 *     database?: string,
 *     application-name?: string,
 *     ssl-mode?: PostgresSslMode,
 *     pooled?: bool,
 *     max-connections?: positive-int,
 *     idle-timeout?: positive-int,
 *     reset-connections?: bool,
 * }
 * @psalm-type DatabaseConfiguration = MysqlConnectionConfiguration|PostgresConnectionConfiguration
 * @psalm-type Configuration = array{
 *     default?: non-empty-string,
 *     databases?: array<non-empty-string, DatabaseConfiguration>
 * }
 */
final class DatabaseExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(RegistryInterface $registry, DocumentInterface $configurations): void
    {
        $configuration = $configurations->getOfTypeOrDefault('database', $this->getConfigurationType(), []);

        $databases = $configuration['databases'] ?? [];

        $databaseDefinitions = $this->registerDatabases($registry, $databases);

        if ($databaseDefinitions !== []) {
            $defaultDatabase = $configuration['default'] ?? array_key_first($databaseDefinitions);

            $this->registerDatabaseManager($registry, $defaultDatabase, $databaseDefinitions);
        }
    }

    /**
     * Register databases.
     *
     * @param RegistryInterface $registry
     * @param array<non-empty-string, DatabaseConfiguration> $databases
     *
     * @return array<non-empty-string, non-empty-string> Map of database names to database service IDs
     */
    private function registerDatabases(RegistryInterface $registry, array $databases): array
    {
        $databaseDefinitions = [];

        foreach ($databases as $name => $config) {
            $connectionServiceId = 'database.connection.' . $name;
            $databaseServiceId = 'database.' . $name;

            if ($config['platform'] === 'mysql' || $config['platform'] === 'mysqli' || $config['platform'] === 'mariadb') {
                /** @var MysqlConnectionConfiguration $config */
                $this->registerMysqlConnection($registry, $connectionServiceId, $config);
            } else {
                /** @var PostgresConnectionConfiguration $config */
                $this->registerPostgresConnection($registry, $connectionServiceId, $config);
            }

            $registry->addDefinition(Definition::create($databaseServiceId, DatabaseInterface::class, new DatabaseFactory(
                connection: $connectionServiceId,
            )));

            $databaseDefinitions[$name] = $databaseServiceId;
        }

        return $databaseDefinitions;
    }

    /**
     * Register a MySQL connection.
     *
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     * @param MysqlConnectionConfiguration $config
     *
     * @throws RuntimeException If the "amphp/mysql" package is not installed.
     */
    private function registerMysqlConnection(RegistryInterface $registry, string $serviceId, array $config): void
    {
        if (!Class\exists(MysqlConnectionPool::class)) {
            throw new RuntimeException('The "amphp/mysql" package is required to use the mysql database connection.');
        }

        $pooled = $config['pooled'] ?? true;
        if ($pooled) {
            $registry->addDefinition(Definition::create($serviceId, MysqlConnectionPool::class, new MysqlConnectionPoolFactory(
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
            )));

            return;
        }

        $registry->addDefinition(Definition::create($serviceId, MysqlConnection::class, new MysqlConnectionFactory(
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
     * @param RegistryInterface $registry
     * @param non-empty-string $serviceId
     * @param PostgresConnectionConfiguration $config
     *
     * @throws RuntimeException If the "amphp/postgres" package is not installed.
     */
    private function registerPostgresConnection(RegistryInterface $registry, string $serviceId, array $config): void
    {
        if (!Class\exists(PostgresConnectionPool::class)) {
            throw new RuntimeException('The "amphp/postgres" package is required to use the postgres database connection.');
        }

        $pooled = $config['pooled'] ?? true;
        if ($pooled) {
            $registry->addDefinition(Definition::create($serviceId, PostgresConnectionPool::class, new PostgresConnectionPoolFactory(
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

        $registry->addDefinition(Definition::create($serviceId, PostgresConnection::class, new PostgresConnectionFactory(
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
     * @param RegistryInterface $registry
     * @param non-empty-string $defaultDatabase
     * @param array<non-empty-string, non-empty-string> $databaseDefinitions
     */
    private function registerDatabaseManager(RegistryInterface $registry, string $defaultDatabase, array $databaseDefinitions): void
    {
        $definition = Definition::ofType(DatabaseManager::class, new DatabaseManagerFactory(
            defaultDatabaseId: $defaultDatabase,
            services: $databaseDefinitions,
        ));

        $definition->addAlias(DatabaseManagerInterface::class);

        $registry->addDefinition($definition);
    }

    /**
     * @return Type\TypeInterface<Configuration>
     */
    public function getConfigurationType(): Type\TypeInterface
    {
        return Type\shape([
            'default' => Type\optional(Type\non_empty_string()),
            'databases' => Type\optional(Type\dict(
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
                        'max-connections' => Type\optional(Type\positive_int()),
                        'idle-timeout' => Type\optional(Type\positive_int()),
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
                        'ssl-mode' => Type\optional(Type\union(
                            Type\literal_scalar('disable'),
                            Type\literal_scalar('allow'),
                            Type\literal_scalar('prefer'),
                            Type\literal_scalar('require'),
                            Type\literal_scalar('verify-ca'),
                            Type\literal_scalar('verify-full')
                        )),
                        'pooled' => Type\optional(Type\bool()),
                        'max-connections' => Type\optional(Type\positive_int()),
                        'idle-timeout' => Type\optional(Type\positive_int()),
                        'reset-connections' => Type\optional(Type\bool()),
                    ])
                ),
            )),
        ]);
    }
}
