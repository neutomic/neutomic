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

use Neu\Component\Http\Exception\RuntimeException;

/**
 * Defines the contract for a cluster worker.
 *
 * This interface provides methods for starting and stopping a worker in a clustered server environment.
 */
interface ClusterWorkerInterface extends SharedResourcesInterface
{
    /**
     * Determines whether we are in a cluster worker context.
     *
     * If this method returns false, then any attempt to start or stop the worker will throw an exception.
     *
     * @return bool True if we are in a cluster worker context, false otherwise.
     */
    public function isInWorkerContext(): bool;

    /**
     * Return the identifier of the worker.
     *
     * @return int The worker identifier.
     *
     * @throws RuntimeException If the worker is not in a cluster worker context.
     */
    public function getWorkerId(): int;

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
