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

namespace Neu\Component\Http\Server;

/**
 * ClusterInterface defines the methods required to manage a cluster of worker processes.
 *
 * Implementations of this interface should provide mechanisms to start and stop the cluster.
 */
interface ClusterInterface
{
    /**
     * Start the cluster of worker processes.
     *
     * @param int|null $workers The number of workers to start. If null, the implementation should
     *                          determine the number of workers, which may be pre-configured or based on other criteria.
     */
    public function start(null|int $workers = null): void;

    /**
     * Restart the cluster of worker processes.
     */
    public function restart(): void;

    /**
     * Stop the cluster of worker processes.
     */
    public function stop(): void;
}
