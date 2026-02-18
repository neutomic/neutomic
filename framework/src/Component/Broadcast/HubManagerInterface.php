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

namespace Neu\Component\Broadcast;

interface HubManagerInterface
{
    /**
     * Retrieve the default hub.
     *
     * @throws Exception\RuntimeException If failed to load the default hub.
     *
     * @return HubInterface The default hub.
     */
    public function getDefaultHub(): HubInterface;

    /**
     * Check if a hub with the given identifier is registered.
     *
     * @param non-empty-string $identifier The unique identifier for the hub.
     *
     * @return bool True if the hub is registered, false otherwise.
     */
    public function hasHub(string $identifier): bool;

    /**
     * Retrieve a hub by its identifier.
     *
     * @param non-empty-string $identifier The unique identifier for the hub.
     *
     * @throws Exception\HubNotFoundException If the hub with the given identifier is not found.
     * @throws Exception\RuntimeException If failed to load the hub.
     *
     * @return HubInterface The hub associated with the given identifier.
     */
    public function getHub(string $identifier): HubInterface;

    /**
     * Retrieve all available hubs.
     *
     * @throws Exception\RuntimeException If failed to load the hubs.
     *
     * @return list<HubInterface> A list of all available hubs.
     */
    public function getAvailableHubs(): array;
}
