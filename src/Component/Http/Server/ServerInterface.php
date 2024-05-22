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

use Neu\Component\Http\Exception\MisconfiguredServerException;
use Neu\Component\Http\Exception\ServerStateConflictException;

/**
 * Defines the contract for an HTTP server.
 *
 * This interface provides methods for starting, running, and stopping the server,
 * as well as accessing its configuration and current status.
 */
interface ServerInterface
{
    /**
     * Retrieves the current status of the server.
     *
     * @return Status The operational status of the server, indicating if it is stopped, running, or in another state.
     */
    public function getStatus(): Status;

    /**
     * Starts the server.
     *
     * This method initializes the server to begin handling requests but returns control immediately,
     * allowing further code execution.
     *
     * If the server status is {@see Status::Started}, this method has no effect, otherwise if the status
     * is anything other than {@see Status::Stopped}, this method raises an exception.
     *
     * @throws MisconfiguredServerException If the server is not properly configured.
     * @throws ServerStateConflictException If the server is in a conflicting state.
     */
    public function start(): void;

    /**
     * Stops the server.
     *
     * This method terminates the server operations, closing all active connections and freeing associated resources.
     *
     * If the server status is {@see Status::Stopped}, this method has no effect, otherwise if the status
     * is anything other than {@see Status::Started}, this method raises an exception.
     *
     * @throws ServerStateConflictException If the server is in a conflicting state.
     */
    public function stop(): void;
}
