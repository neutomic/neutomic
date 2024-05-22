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
 * Event triggered when a cluster worker is stopped.
 */
final readonly class ClusterWorkerStoppedEvent
{
    public int $workerId;

    /**
     * Create a new {@see ClusterWorkerStoppedEvent} instance.
     *
     * @param int $workerId The ID of the worker that has stopped.
     */
    public function __construct(int $workerId)
    {
        $this->workerId = $workerId;
    }
}
