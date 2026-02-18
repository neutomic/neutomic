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

namespace Neu\Component\Password;

interface HasherManagerInterface
{
    /**
     * Retrieve the default password hasher.
     *
     * @throws Exception\RuntimeException If failed to load the default password hasher.
     *
     * @return HasherInterface The default password hasher.
     */
    public function getDefaultHasher(): HasherInterface;

    /**
     * Check if a password hasher with the given identifier is registered.
     *
     * @param non-empty-string $identifier The unique identifier for the password hasher.
     *
     * @return bool True if the password hasher is registered, false otherwise.
     */
    public function hasHasher(string $identifier): bool;

    /**
     * Retrieve a password hasher by its identifier.
     *
     * @param non-empty-string $identifier The unique identifier for the password hasher.
     *
     * @throws Exception\RuntimeException If failed to load the password hasher.
     * @throws Exception\HasherNotFoundException If the password hasher with the given identifier is not found.
     *
     * @return HasherInterface The password hasher associated with the given identifier.
     */
    public function getHasher(string $identifier): HasherInterface;

    /**
     * Retrieve the list of available password hashers.
     *
     * @throws Exception\RuntimeException If failed to load the list of available password hashers.
     *
     * @return array<string, HasherInterface> The array of available password hashers, indexed by their unique identifiers.
     */
    public function getAvailableHashers(): array;
}
