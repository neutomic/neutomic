<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Event;

/**
 * Event triggered when a cluster worker is started.
 */
final readonly class ClusterWorkerStartedEvent
{
    public int $workerId;

    /**
     * Create a new {@see ClusterWorkerStartedEvent} instance.
     *
     * @param int $workerId The ID of the worker that has started.
     */
    public function __construct(int $workerId)
    {
        $this->workerId = $workerId;
    }
}
