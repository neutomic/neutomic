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

namespace Neu\Tests\Component\EventDispatcher;

use Amp;
use Neu\Component\EventDispatcher\EventDispatcher;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\Registry;
use Neu\Tests\Component\EventDispatcher\Fixture\Event\OrderCreatedEvent;
use Neu\Tests\Component\EventDispatcher\Fixture\Event\OrderUpdatedEvent;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\DateTime\Duration;

final class EventDispatcherTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testDispatch(): void
    {
        $event = new OrderCreatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturn($event);

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturn($event);

        $registry = new Registry();
        $registry->register(OrderCreatedEvent::class, $listener1);
        $registry->register(OrderCreatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($registry);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    /**
     * @throws Exception
     */
    public function testDispatchListenerStopsEvent(): void
    {
        $event = new OrderUpdatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::once())
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event): OrderUpdatedEvent {
                $event->stopped = true;

                return $event;
            });

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');

        $registry = new Registry();
        $registry->register(OrderUpdatedEvent::class, $listener1);
        $registry->register(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($registry);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    /**
     * @throws Exception
     */
    public function testDispatchStoppedEvent(): void
    {
        $event = new OrderUpdatedEvent(1);
        $event->stopped = true;

        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1->expects(static::never())->method('process');
        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2->expects(static::never())->method('process');

        $registry = new Registry();
        $registry->register(OrderUpdatedEvent::class, $listener1);
        $registry->register(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($registry);

        $returned = $dispatcher->dispatch($event);

        static::assertSame($returned, $event);
    }

    /**
     * @throws Exception
     */
    public function testDispatchingTheSameEventConcurrently(): void
    {
        $ref = new Psl\Ref('');
        $event = new OrderUpdatedEvent(1);
        $listener1 = $this->createMock(ListenerInterface::class);
        $listener1
            ->expects(static::exactly(2))
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event) use ($ref): OrderUpdatedEvent {
                $event->orderId++;
                $ref->value .= '1';

                Async\sleep(Duration::milliseconds(20));

                return $event;
            });

        $listener2 = $this->createMock(ListenerInterface::class);
        $listener2
            ->expects(static::exactly(2))
            ->method('process')
            ->with($event)
            ->willReturnCallback(static function (OrderUpdatedEvent $event) use ($ref): OrderUpdatedEvent {
                $event->orderId++;
                $ref->value .= '2';

                Async\sleep(Duration::milliseconds(20));

                return $event;
            });

        $provider = new Registry();
        $provider->register(OrderUpdatedEvent::class, $listener1);
        $provider->register(OrderUpdatedEvent::class, $listener2);

        $dispatcher = new EventDispatcher($provider);

        [$errors, [$one, $two]] = Amp\Future\awaitAll([
            Amp\async(static fn () =>  $dispatcher->dispatch($event)),
            Amp\async(static fn () =>  $dispatcher->dispatch($event)),
        ]);

        static::assertSame([], $errors);
        static::assertSame($one, $event);
        static::assertSame($two, $event);
        static::assertSame('1212', $ref->value);
    }
}
