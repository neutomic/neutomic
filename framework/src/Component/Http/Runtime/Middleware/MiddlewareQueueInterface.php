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

namespace Neu\Component\Http\Runtime\Middleware;

use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Represents a middleware queue (FIFO) that can be used to wrap a handler with multiple middlewares.
 */
interface MiddlewareQueueInterface
{
    /**
     * Enqueues a middleware into the queue.
     *
     * @param MiddlewareInterface $middleware The middleware to push into the queue.
     * @param int $priority The priority of the middleware.
     *
     * If the middleware implements the {@see PrioritizedMiddlewareInterface}, the priority will be taken from the middleware.
     */
    public function enqueue(MiddlewareInterface $middleware, int $priority = 0): void;

    /**
     * Wraps a handler with all the queued middlewares.
     *
     * @param HandlerInterface $handler The handler interface to wrap.
     *
     * @return HandlerInterface The handler interface wrapped in all the queued middlewares.
     */
    public function wrap(HandlerInterface $handler): HandlerInterface;
}
