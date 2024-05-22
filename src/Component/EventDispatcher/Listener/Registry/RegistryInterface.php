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

namespace Neu\Component\EventDispatcher\Listener\Registry;

use Neu\Component\EventDispatcher\Listener\ListenerInterface;

/**
 * Defines the interface for a listener registry.
 *
 * This interface provides methods for registering listeners, checking their existence,
 * and retrieving them.
 *
 * It also allows incorporating another registry's entries into this one.
 */
interface RegistryInterface
{
    /**
     * Registers a listener for the specified event.
     *
     * @template T of object
     *
     * @param class-string<T> $event The name of the event to listen for.
     * @param ListenerInterface<T> $listener The listener to register.
     * @param int $priority The priority of the listener.
     */
    public function register(string $event, ListenerInterface $listener, int $priority = 0): void;

    /**
     * Incorporates the entries from another registry into this one.
     *
     * This method integrates all listeners from the specified registry into the current registry.
     *
     * The source registry remains unmodified.
     *
     * @param RegistryInterface $registry The registry whose entries are to be incorporated.
     */
    public function incorporate(RegistryInterface $registry): void;

    /**
     * Checks if listeners are registered for the specified event.
     *
     * @param class-string $name The name of the event to check.
     *
     * @return bool Returns true if listeners are registered for the event, false otherwise.
     */
    public function has(string $name): bool;

    /**
     * Retrieves all listeners registered for the specified event.
     *
     * @template T of object
     *
     * @param class-string<T> $name The name of the event to retrieve listeners for.
     *
     * @return iterable<ListenerInterface<T>> A list of listeners registered for the event.
     */
    public function getListeners(string $name): iterable;

    /**
     * Retrieves all listeners registered in the registry.
     *
     * @return iterable<int, array<class-string, list<ListenerInterface<object>>>> A map of priorities to events and their listeners.
     */
    public function getRegisteredListeners(): iterable;
}
