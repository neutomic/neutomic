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

interface StoreManagerInterface
{
    /**
     * Retrieve the default store.
     *
     * @throws Exception\RuntimeException If failed to load the default store.
     *
     * @return StoreInterface The default store.
     */
    public function getDefaultStore(): StoreInterface;

    /**
     * Check if a store with the given identifier is registered.
     *
     * @param non-empty-string $identifier The unique identifier for the store.
     *
     * @return bool True if the store is registered, false otherwise.
     */
    public function hasStore(string $identifier): bool;

    /**
     * Retrieve a store by its identifier.
     *
     * @param non-empty-string $identifier The unique identifier for the store.
     *
     * @throws Exception\StoreNotFoundException If the store with the given identifier is not found.
     * @throws Exception\RuntimeException If failed to load the store.
     *
     * @return StoreInterface The store associated with the given identifier.
     */
    public function getStore(string $identifier): StoreInterface;
}
