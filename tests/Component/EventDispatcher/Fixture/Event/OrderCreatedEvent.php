<?php

declare(strict_types=1);

namespace Neu\Tests\Component\EventDispatcher\Fixture\Event;

final readonly class OrderCreatedEvent
{
    public function __construct(
        public int $orderId
    ) {
    }
}
