<?php

declare(strict_types=1);

namespace Neu\Component\EventDispatcher\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Listener
{
    /**
     * The list of events this listener is listening to.
     *
     * @var non-empty-list<class-string>
     */
    public array $events;

    /**
     * The priority of this listener.
     *
     * @var int
     */
    public int $priority;

    /**
     * Create a new listener attribute.
     *
     * @param non-empty-list<class-string>|class-string $events The list of events this listener is listening to.
     * @param int $priority The priority of this listener.
     */
    public function __construct(array|string $events, int $priority = 0)
    {
        $this->events = (array) $events;
        $this->priority = $priority;
    }
}
