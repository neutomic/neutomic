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

final readonly class ClusterStartedEvent
{
    /**
     * The number of workers in the cluster.
     *
     * @var int
     */
    public int $workers;

    /**
     * Create a new {@see ClusterStartedEvent} instance.
     *
     * @param int $workers The number of workers in the cluster.
     */
    public function __construct(int $workers)
    {
        $this->workers = $workers;
    }
}
