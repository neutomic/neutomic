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
use Neu\Component\Http\Runtime\Handler\MiddlewareHandler;
use Psl\DataStructure;
use WeakMap;
use Override;

/**
 * A queue for managing and prioritizing middleware.
 *
 * This class implements the {@see MiddlewareQueueInterface} and provides functionality to enqueue
 * middleware with a specific priority and to wrap a handler with the queued middleware.
 *
 * @psalm-suppress PropertyTypeCoercion
 * @psalm-suppress MixedPropertyTypeCoercion
 * @psalm-suppress PossiblyNullArgument
 */
final class MiddlewareQueue implements MiddlewareQueueInterface
{
    /**
     * The priority queue for middleware.
     *
     * @var DataStructure\PriorityQueue<MiddlewareInterface>
     */
    private DataStructure\PriorityQueue $queue;

    /**
     * Cache for wrapped handlers to avoid redundant wrapping.
     *
     * @var WeakMap<HandlerInterface, HandlerInterface>
     */
    private WeakMap $cache;

    /**
     * Create a new {@see MiddlewareQueue} instance.
     *
     * Initializes a new instance of the MiddlewareQueue with an empty priority queue and cache.
     */
    public function __construct()
    {
        $this->queue = new DataStructure\PriorityQueue();
        $this->cache = new WeakMap();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function enqueue(MiddlewareInterface $middleware, int $priority = 0): void
    {
        if ($middleware instanceof PrioritizedMiddlewareInterface) {
            $priority = $middleware->getPriority();
        }

        $this->queue->enqueue($middleware, $priority);
        $this->cache = new WeakMap();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function wrap(HandlerInterface $handler): HandlerInterface
    {
        /** @var HandlerInterface */
        return $this->cache[$handler] ??= $this->doWrap($handler);
    }

    /**
     * Internal method to wrap a handler with the middleware in the queue.
     *
     * Clones the queue to preserve the original queue state.
     *
     * @param HandlerInterface $handler The handler to wrap.
     *
     * @return HandlerInterface The wrapped handler.
     */
    private function doWrap(HandlerInterface $handler): HandlerInterface
    {
        $queue = clone $this->queue;
        while ($middleware = $queue->pull()) {
            $handler = new MiddlewareHandler($middleware, $handler);
        }

        return $handler;
    }
}
