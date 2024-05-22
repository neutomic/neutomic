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
