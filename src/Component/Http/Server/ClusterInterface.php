<?php

declare(strict_types=1);

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
    public function start(?int $workers = null): void;

    /**
     * Restart the cluster of worker processes.
     */
    public function restart(): void;

    /**
     * Stop the cluster of worker processes.
     */
    public function stop(): void;
}
