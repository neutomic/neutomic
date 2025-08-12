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

namespace Neu\Component\Cache\Driver;

use Amp\Redis\Command\Option\SetOptions;
use Amp\Redis\RedisClient;
use Amp\Redis\RedisException;
use Neu\Component\Cache\Exception\RuntimeException;
use Neu\Component\Cache\Exception\UnavailableItemException;

final class RedisDriver implements DriverInterface
{
    use SerializationTrait;

    /**
     * The redis client.
     *
     * @var null|RedisClient
     */
    private null|RedisClient $client;

    /**
     * Creates a new redis cache driver.
     *
     * @param RedisClient $client The redis client.
     */
    public function __construct(RedisClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function get(string $key): mixed
    {
        if (null === $this->client) {
            throw new RuntimeException('The redis client has been closed.');
        }

        $client = $this->client;

        try {
            if (!$client->has($key)) {
                throw UnavailableItemException::for($key);
            }

            $value = $client->get($key);
            if ('' === $value || null === $value) {
                throw UnavailableItemException::for($key);
            }

            return $this->unserialize($key, $value);
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while reading the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function set(string $key, mixed $value, null|int $ttl = null): void
    {
        if (null === $this->client) {
            throw new RuntimeException('The redis client has been closed.');
        }

        $client = $this->client;

        $options = new SetOptions();
        if ($ttl !== null) {
            $options = $options->withTtl($ttl);
        }

        try {
            $client->set($key, $this->serialize($key, $value), $options);
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while writing to the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function delete(string $key): void
    {
        if (null === $this->client) {
            throw new RuntimeException('The redis client has been closed.');
        }

        $client = $this->client;

        try {
            $client->delete($key);
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while deleting the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function clear(): void
    {
        if (null === $this->client) {
            throw new RuntimeException('The redis client has been closed.');
        }

        $client = $this->client;

        try {
            $client->flushAll();
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while clearing the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function prune(): void
    {
        // Redis automatically prunes expired keys.
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        $this->client = null;
    }
}
