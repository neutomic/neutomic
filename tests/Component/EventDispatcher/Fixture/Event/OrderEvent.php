<?php

declare(strict_types=1);

namespace Neu\Tests\Component\EventDispatcher\Fixture\Event;

class OrderEvent
{
    public function __construct(
        public int $orderId
    ) {
    }
}
