<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher;

use Psr\EventDispatcher;

/**
 * All implementations of this interface *MUST* be atomic per event.
 */
interface EventDispatcherInterface extends EventDispatcher\EventDispatcherInterface
{
    /**
     * Provide all relevant listeners with an event to process.
     *
     * @template T of object
     *
     * @param T $event The event object to process.
     *
     * @return T The event object that was passed, now modified by listeners.
     */
    public function dispatch(object $event): object;
}
