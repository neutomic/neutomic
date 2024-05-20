<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher;

/**
 * An object that has an event dispatcher.
 */
interface EventDispatcherAwareInterface
{
    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}
