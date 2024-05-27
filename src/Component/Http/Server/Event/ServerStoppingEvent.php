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

namespace Neu\Component\Http\Server\Event;

/**
 * The {@see ServerStoppingEvent} class represents an event that is dispatched when the server is in the process of stopping.
 *
 * This event is triggered as part of the server shutdown sequence to notify listeners that the server is about to
 * cease all operations. It can be used to perform cleanup, save state, or notify other components that the server
 * is no longer going to be active.
 */
final readonly class ServerStoppingEvent
{
}
