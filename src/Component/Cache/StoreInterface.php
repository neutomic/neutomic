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

namespace Neu\Component\Cache;

use Closure;

/**
 * An interface that describes a store.
 */
interface StoreInterface
{
    /**
     * Gets a value associated with the given key.
     *
     * If the specified key doesn't exist, an exception will be thrown.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before returning the value.
     *
     * @note that this guarantee is only provided within the current thread. If a different thread attempts to perform
     *      an operation on the same key concurrently, atomicity is not guaranteed.
     *
     * @param non-empty-string $key The unique cache key of the item to get.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\UnavailableItemException If the value associated with the key is not available.
     * @throws Exception\RuntimeException If an error occurs while getting the value.
     *
     * @return mixed The value associated with the key.
     */
    public function get(string $key): mixed;

    /**
     * Gets a value associated with the given key.
     *
     * If the specified key doesn't exist, `$computer` will be used to compute the value,
     * which will be stored in cache, and returned as a result of this method.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before computing the value.
     *
     * @note that this guarantee is only provided within the current thread. If a different thread attempts to perform
     *      an operation on the same key concurrently, atomicity is not guaranteed.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\RuntimeException If an error occurs while getting the value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function compute(string $key, Closure $computer, null|int $ttl = null): mixed;

    /**
     * Update the value associated with the unique key.
     *
     * Unlike {@see compute()}, `$computer` will always be invoked to compute the value.
     *
     * The resulted value will be stored, and returned as a result of this method.
     *
     * If `$key` doesn't exist in cache, it will be set.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before computing the value.
     *
     * @note that this guarantee is only provided within the current thread. If a different thread attempts to perform
     *      an operation on the same key concurrently, atomicity is not guaranteed.
     *
     * @template T
     *
     * @param non-empty-string $key
     * @param Closure(null|T): T $computer
     * @param positive-int|null $ttl
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\RuntimeException If an error occurs while getting the value.
     * @throws Exception\InvalidValueException If the value return from $computer cannot be stored in cache.
     *
     * @return T
     */
    public function update(string $key, Closure $computer, null|int $ttl = null): mixed;

    /**
     * Delete an item from the cache by its unique key.
     *
     * If the item doesn't exist, this method should do nothing.
     *
     * Implementations must ensure that all pending operations related to the given key are completed before deleting the item.
     *
     * @note that this guarantee is only provided within the current thread. If a different thread attempts to perform
     *      an operation on the same key concurrently, atomicity is not guaranteed.
     *
     * @param non-empty-string $key The unique cache key of the item to delete.
     *
     * @throws Exception\InvalidKeyException If the $key string is not a legal value.
     * @throws Exception\RuntimeException If an error occurs while deleting the item.
     */
    public function delete(string $key): void;

    /**
     * Close the store, releasing any resources.
     *
     * @throws Exception\RuntimeException If an error occurs while closing the store.
     */
    public function close(): void;
}
