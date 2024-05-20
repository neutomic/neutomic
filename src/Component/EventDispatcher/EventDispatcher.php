<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher;

use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface;
use Psl\Async;
use Psr\EventDispatcher\StoppableEventInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    /**
     * The registry of listeners to dispatch events to.
     */
    private RegistryInterface $registry;

    /**
     * The sequence of listeners to dispatch events to.
     *
     * @var Async\KeyedSequence<class-string, object, object>
     */
    private Async\KeyedSequence $sequence;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        $this->sequence = new Async\KeyedSequence(
            /**
             * @param class-string $_event_type
             */
            function (string $_event_type, object $event): object {
                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    return $event;
                }

                $listeners = $this->registry->getListeners($event::class);
                foreach ($listeners as $listener) {
                    if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                        return $event;
                    }

                    $event = $listener->process($event);
                }

                return $event;
            }
        );
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @template T of object
     *
     * @param T $event The event object to process.
     *
     * @return T The event object that was passed, now modified by listeners.
     */
    public function dispatch(object $event): object
    {
        /** @var T */
        return $this->sequence->waitFor($event::class, $event);
    }
}
