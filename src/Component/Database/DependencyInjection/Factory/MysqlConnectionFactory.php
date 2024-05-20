<?php

declare(strict_types=1);

namespace Neu\Component\Database\DependencyInjection\Factory;

use Amp\Mysql;
use Amp\Mysql\MysqlConnection;
use Amp\Sql\SqlException;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see MysqlConnection} instance.
 *
 * @implements FactoryInterface<MysqlConnection>
 */
final readonly class MysqlConnectionFactory implements FactoryInterface
{
    /**
     * The hostname of the MySQL server.
     */
    private string $host;

    /**
     * The port of the MySQL server.
     */
    private ?int $port;

    /**
     * The username for the MySQL connection.
     */
    private ?string $user;

    /**
     * The password for the MySQL connection.
     */
    private ?string $password;

    /**
     * The database name for the MySQL connection.
     */
    private ?string $database;

    /**
     * The character set for the MySQL connection.
     */
    private ?string $charset;

    /**
     * The collation for the MySQL connection.
     */
    private ?string $collate;

    /**
     * The SQL mode for the MySQL connection.
     */
    private ?string $sqlMode;

    /**
     * Whether to use compression for the connection.
     */
    private ?bool $useCompression;

    /**
     * The private key to use for sha256_password authentication method.
     */
    private ?string $key;

    /**
     * Whether to use local infile for the connection.
     */
    private ?bool $useLocalInfile;

    /**
     * Create a new MySQL connection factory.
     *
     * @param string $host The hostname of the MySQL server.
     * @param int|null $port The port of the MySQL server.
     * @param string|null $user The username for the MySQL connection.
     * @param string|null $password The password for the MySQL connection.
     * @param string|null $database The database name for the MySQL connection.
     * @param string|null $charset The character set for the MySQL connection.
     * @param string|null $collate The collation for the MySQL connection.
     * @param string|null $sqlMode The SQL mode for the MySQL connection.
     * @param bool|null $useCompression Whether to use compression for the connection.
     * @param string|null $key The private key to use for sha256_password authentication method.
     * @param bool|null $useLocalInfile Whether to use local infile for the connection.
     */
    public function __construct(
        string $host,
        ?int $port = null,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?string $charset = null,
        ?string $collate = null,
        ?string $sqlMode = null,
        ?bool $useCompression = null,
        ?string $key = null,
        ?bool $useLocalInfile = null
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
        $this->collate = $collate;
        $this->sqlMode = $sqlMode;
        $this->useCompression = $useCompression;
        $this->key = $key;
        $this->useLocalInfile = $useLocalInfile;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): MysqlConnection
    {
        $config = new Mysql\MysqlConfig(
            host: $this->host,
            port: $this->port ?? Mysql\MysqlConfig::DEFAULT_PORT,
            user: $this->user,
            password: $this->password,
            database: $this->database,
            charset: $this->charset,
            collate: $this->collate,
            sqlMode: $this->sqlMode,
            useCompression: $this->useCompression,
            key: $this->key,
            useLocalInfile: $this->useLocalInfile
        );

        try {
            return Mysql\connect($config);
        } catch (SqlException $e) {
            throw new InvalidArgumentException('Failed to connect to the database: ' . $e->getMessage(), 0, $e);
        }
    }
}
