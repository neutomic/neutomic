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
use Psl\Iter;
use Psl\Vec;

use function is_subclass_of;

final class Registry implements RegistryInterface
{
    /**
     * @var array<int, array<class-string, list<ListenerInterface>>>
     *
     * A map of event class names to their listeners organized by priority.
     */
    private array $listeners = [];

    /**
     * @var array<class-string, list<ListenerInterface>>
     */
    private array $optimized = [];

    /**
     * @inheritDoc
     */
    public function register(string $event, ListenerInterface $listener, int $priority = 0): void
    {
        if (isset($this->listeners[$priority][$event]) && Iter\contains($this->listeners[$priority][$event], $listener)) {
            return;
        }

        // Reset the optimized cache, as the listeners have changed.
        $this->optimized = [];

        $priorities = $this->listeners[$priority] ?? [];
        $events = $priorities[$event] ?? [];
        $events[] = $listener;
        $priorities[$event] = $events;
        $this->listeners[$priority] = $priorities;
    }

    /**
     * @inheritDoc
     */
    public function incorporate(RegistryInterface $registry): void
    {
        foreach ($registry->getRegisteredListeners() as $priority => $events) {
            foreach ($events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $this->register($event, $listener, $priority);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        foreach ($this->listeners as $events) {
            if (Iter\contains_key($events, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves all listeners registered for the specified event.
     *
     * @template T of object
     *
     * @param class-string<T> $name The name of the event to retrieve listeners for.
     *
     * @return iterable<ListenerInterface<T>> A list of listeners registered for the event.
     */
    public function getListeners(string $name): iterable
    {
        if (Iter\contains_key($this->optimized, $name)) {
            /** @var list<ListenerInterface<T>> */
            return $this->optimized[$name];
        }

        $priorities = Vec\sort(
            Vec\keys($this->listeners),
            static fn (int $a, int $b): int => $a <=> $b,
        );

        $result = [];
        foreach ($priorities as $priority) {
            foreach ($this->listeners[$priority] as $event => $listeners) {
                if ($name === $event || is_subclass_of($name, $event)) {
                    foreach ($listeners as $listener) {
                        $result[] = $listener;
                    }
                }
            }
        }

        $this->optimized[$name] = $result;

        /** @var list<ListenerInterface<T>> */
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getRegisteredListeners(): iterable
    {
        return $this->listeners;
    }
}
