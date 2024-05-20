<?php

declare(strict_types=1);

namespace Neu\Component\Cache\DependencyInjection\Factory\Driver;

use Amp\Redis\RedisConfig;
use Amp\Redis\RedisException;
use Neu\Component\Cache\Driver\RedisDriver;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\InvalidArgumentException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

use function Amp\Redis\createRedisClient;

/**
 * A factory for creating a {@see RedisDriver} instance.
 *
 * @implements FactoryInterface<RedisDriver>
 */
final readonly class RedisDriverFactory implements FactoryInterface
{
    private string $uri;
    private ?int $timeout;
    private ?int $database;
    private ?string $password;

    /**
     * @param string $uri The URI to connect to.
     * @param null|int $timeout The connection timeout in milliseconds.
     * @param null|int $database The database to select.
     * @param null|string $password The password to authenticate with.
     */
    public function __construct(string $uri, ?int $timeout = null, ?int $database = null, ?string $password = null)
    {
        $this->uri = $uri;
        $this->timeout = $timeout;
        $this->database = $database;
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): RedisDriver
    {
        try {
            $config = RedisConfig::fromUri($this->uri, $this->timeout ?? RedisConfig::DEFAULT_TIMEOUT);
        } catch (RedisException $e) {
            throw new InvalidArgumentException('Failed to create Redis configuration from URI: ' . $e->getMessage(), 0, $e);
        }

        if (null !== $this->database) {
            $config = $config->withDatabase($this->database);
        }

        if (null !== $this->password) {
            $config = $config->withPassword($this->password);
        }

        return new RedisDriver(
            client: createRedisClient($config)
        );
    }
}
