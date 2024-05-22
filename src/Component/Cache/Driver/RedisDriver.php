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

    private RedisClient $client;

    public function __construct(RedisClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        try {
            if (!$this->client->has($key)) {
                throw UnavailableItemException::for($key);
            }

            $value = $this->client->get($key);
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
    public function set(string $key, mixed $value, null|int $ttl = null): void
    {
        $options = new SetOptions();
        if ($ttl !== null) {
            $options = $options->withTtl($ttl);
        }

        try {
            $this->client->set($key, $this->serialize($key, $value), $options);
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while writing to the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        try {
            $this->client->delete($key);
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while deleting the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        try {
            $this->client->flushAll();
        } catch (RedisException $e) {
            throw new RuntimeException('A redis error occurred while clearing the cache.', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function prune(): void
    {
        // Redis automatically prunes expired keys.
    }
}
