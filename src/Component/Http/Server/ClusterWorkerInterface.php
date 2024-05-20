<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server;

use Neu\Component\Http\Exception\RuntimeException;

/**
 * Defines the contract for a cluster worker.
 *
 * This interface provides methods for starting and stopping a worker in a clustered server environment.
 */
interface ClusterWorkerInterface
{
    /**
     * Starts the worker.
     *
     * This method initializes the worker to begin handling requests within the cluster.
     *
     * @throws RuntimeException If the worker is not in a cluster worker context.
     */
    public function start(): void;

    /**
     * Stops the worker.
     *
     * This method terminates the worker's operations, closing all active connections and freeing associated resources.
     *
     * @throws RuntimeException If the worker is not in a cluster worker context.
     */
    public function stop(): void;
}
