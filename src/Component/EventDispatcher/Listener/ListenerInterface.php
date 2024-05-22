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

namespace Neu\Component\EventDispatcher\Listener;

/**
 * @template T of object
 */
interface ListenerInterface
{
    /**
     * Process the given event.
     *
     * @param T $event The event object to process.
     *
     * @return T The event object that was passed, now modified by the listener.
     */
    public function process(object $event): object;
}
