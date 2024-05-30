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

use Neu\Component\Contract\ParcelManagerInterface;
use Neu\Component\Http\Exception\RuntimeException;

/**
 * Defines the contract for a cluster worker.
 *
 * This interface provides methods for starting and stopping a worker in a clustered server environment.
 */
interface ClusterWorkerInterface extends ParcelManagerInterface
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
