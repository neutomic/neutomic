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

namespace Neu\Component\Database\DependencyInjection\Factory;

use Amp\Postgres;
use Amp\Postgres\PostgresConfig;
use Amp\Sql\Common\SqlCommonConnectionPool;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see Postgres\PostgresConnectionPool} instance.
 *
 * @implements FactoryInterface<Postgres\PostgresConnectionPool>
 */
final readonly class PostgresConnectionPoolFactory implements FactoryInterface
{
    /**
     * The hostname of the Postgres server.
     */
    private string $host;

    /**
     * The port of the Postgres server.
     */
    private null|int $port;

    /**
     * The username for the Postgres connection.
     */
    private null|string $user;

    /**
     * The password for the Postgres connection.
     */
    private null|string $password;

    /**
     * The database name for the Postgres connection.
     */
    private null|string $database;

    /**
     * The application name for the Postgres connection.
     */
    private null|string $applicationName;

    /**
     * The SSL mode for the Postgres connection.
     *
     * @var value-of<PostgresConfig::SSL_MODES>|null
     */
    private null|string $sslMode;

    /**
     * The maximum number of connections in the pool.
     *
     * @var positive-int|null
     */
    private null|int $maxConnections;

    /**
     * The idle timeout for connections in the pool, in seconds.
     *
     * @var positive-int|null
     */
    private null|int $idleTimeout;

    /**
     * Whether to reset connections before returning them to the pool.
     *
     * @var bool|null
     */
    private null|bool $resetConnections;

    /**
     * Create a new Postgres connection pool factory.
     *
     * @param string $host The hostname of the Postgres server.
     * @param int|null $port The port of the Postgres server.
     * @param string|null $user The username for the Postgres connection.
     * @param string|null $password The password for the Postgres connection.
     * @param string|null $database The database name for the Postgres connection.
     * @param string|null $applicationName The application name for the Postgres connection.
     * @param value-of<PostgresConfig::SSL_MODES>|null $sslMode The SSL mode for the Postgres connection.
     * @param positive-int|null $maxConnections The maximum number of connections in the pool.
     * @param positive-int|null $idleTimeout The idle timeout for connections in the pool, in seconds.
     * @param bool|null $resetConnections Whether to reset connections before returning them to the pool.
     */
    public function __construct(
        string $host,
        null|int $port = null,
        null|string $user = null,
        null|string $password = null,
        null|string $database = null,
        null|string $applicationName = null,
        null|string $sslMode = null,
        null|int $maxConnections = null,
        null|int $idleTimeout = null,
        null|bool $resetConnections = null,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->applicationName = $applicationName;
        $this->sslMode = $sslMode;
        $this->maxConnections = $maxConnections;
        $this->idleTimeout = $idleTimeout;
        $this->resetConnections = $resetConnections;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Postgres\PostgresConnectionPool
    {
        $config = new PostgresConfig(
            host: $this->host,
            port: $this->port ?? PostgresConfig::DEFAULT_PORT,
            user: $this->user,
            password: $this->password,
            database: $this->database,
            applicationName: $this->applicationName,
            sslMode: $this->sslMode,
        );

        $maxConnections = $this->maxConnections;
        if ($maxConnections === null) {
            $maxConnections = SqlCommonConnectionPool::DEFAULT_MAX_CONNECTIONS;
        }

        $idleTimeout = $this->idleTimeout;
        if ($idleTimeout === null) {
            $idleTimeout = SqlCommonConnectionPool::DEFAULT_IDLE_TIMEOUT;
        }

        $resetConnections = $this->resetConnections;
        if ($resetConnections === null) {
            $resetConnections = true;
        }

        return new Postgres\PostgresConnectionPool(
            $config,
            $maxConnections,
            $idleTimeout,
            $resetConnections,
        );
    }
}
