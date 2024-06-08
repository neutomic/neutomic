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

namespace Neu\Framework;

/**
 * Enum representing the different modes the Engine can run in.
 */
enum Mode
{
    /**
     * Application mode:
     *
     * In this mode, the engine automatically determines whether to run in Console or HTTP Cluster
     * mode based on the current context. If the process is a worker, it runs in cluster mode;
     * otherwise, it runs the console application.
     */
    case Application;

    /**
     * HTTP Server mode:
     *
     * In this mode, the engine runs an HTTP server. It listens for incoming HTTP requests and
     * processes them. This mode is suitable for running standalone web applications.
     */
    case HttpServer;

    /**
     * HTTP Cluster mode:
     *
     * In this mode, the engine runs in a clustered environment, where it can manage multiple
     * worker processes. This mode is useful for scaling web applications across multiple
     * processes to handle high traffic loads.
     */
    case HttpCluster;

    /**
     * Console Only mode:
     *
     * In this mode, the engine runs a console application. It is typically used for CLI commands
     * and operations that do not require a web server.
     */
    case ConsoleOnly;
}
