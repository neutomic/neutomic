<?php

declare(strict_types=1);

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
    private ?int $port;

    /**
     * The username for the Postgres connection.
     */
    private ?string $user;

    /**
     * The password for the Postgres connection.
     */
    private ?string $password;

    /**
     * The database name for the Postgres connection.
     */
    private ?string $database;

    /**
     * The application name for the Postgres connection.
     */
    private ?string $applicationName;

    /**
     * The SSL mode for the Postgres connection.
     *
     * @var value-of<PostgresConfig::SSL_MODES>|null
     */
    private ?string $sslMode;

    /**
     * The maximum number of connections in the pool.
     *
     * @var positive-int|null
     */
    private ?int $maxConnections;

    /**
     * The idle timeout for connections in the pool, in seconds.
     *
     * @var positive-int|null
     */
    private ?int $idleTimeout;

    /**
     * Whether to reset connections before returning them to the pool.
     *
     * @var bool|null
     */
    private ?bool $resetConnections;

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
        ?int $port = null,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?string $applicationName = null,
        ?string $sslMode = null,
        ?int $maxConnections = null,
        ?int $idleTimeout = null,
        ?bool $resetConnections = null,
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

        return new Postgres\PostgresConnectionPool(
            $config,
            $this->maxConnections ?? SqlCommonConnectionPool::DEFAULT_MAX_CONNECTIONS,
            $this->idleTimeout ?? SqlCommonConnectionPool::DEFAULT_IDLE_TIMEOUT,
            $this->resetConnections ?? true,
        );
    }
}
