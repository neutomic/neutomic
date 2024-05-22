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
    private null|int $timeout;
    private null|int $database;
    private null|string $password;

    /**
     * @param string $uri The URI to connect to.
     * @param null|int $timeout The connection timeout in milliseconds.
     * @param null|int $database The database to select.
     * @param null|string $password The password to authenticate with.
     */
    public function __construct(string $uri, null|int $timeout = null, null|int $database = null, null|string $password = null)
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
