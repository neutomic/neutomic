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

namespace Neu\Tests\Component\EventDispatcher\Fixture\Event;

use Psr\EventDispatcher\StoppableEventInterface;

final class OrderUpdatedEvent extends OrderEvent implements StoppableEventInterface
{
    public bool $stopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
