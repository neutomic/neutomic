<?php

declare(strict_types=1);

namespace Neu\Component\Cache\Driver;

use Neu\Component\Cache\Exception\InvalidKeyException;
use Neu\Component\Cache\Exception\InvalidValueException;
use Neu\Component\Cache\Exception\RuntimeException;
use Neu\Component\Cache\Exception\UnavailableItemException;

/**
 * An interface that describes a store driver.
 */
interface DriverInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param non-empty-string $key The unique key of this item in the cache.
     *
     * @throws UnavailableItemException If $key is not present in the cache.
     * @throws InvalidKeyException If the $key string is not a legal value.
     *
     * @return mixed The value of the item from the cache.
     */
    public function get(string $key): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param non-empty-string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|positive-int $ttl The TTL value of this item.
     *
     * @throws InvalidKeyException If the $key string is not a legal value.
     * @throws InvalidValueException If the $value cannot be stored using this driver.
     */
    public function set(string $key, mixed $value, null|int $ttl = null): void;

    /**
     * Delete an item from the cache by its unique key.
     *
     * If the value is not present within the cache, this method *MUST* return immediately.
     *
     * This method must wait until the item is deleted, rather than deferring the action.
     *
     * @param non-empty-string $key The unique cache key of the item to delete.
     *
     * @throws InvalidKeyException If the $key string is not a legal value.
     */
    public function delete(string $key): void;

    /**
     * Clear all items from the cache.
     *
     * This method must wait until all items are cleared, rather than deferring the action.
     *
     * @throws RuntimeException If an error occurs while clearing the cache.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Clear expired items from the cache.
     *
     * This method must wait until the expired items are pruned, rather than deferring the action.
     *
     * @throws RuntimeException If an error occurs while pruning the cache.
     *
     * @return void
     */
    public function prune(): void;
}
