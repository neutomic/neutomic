<?php

declare(strict_types=1);

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
