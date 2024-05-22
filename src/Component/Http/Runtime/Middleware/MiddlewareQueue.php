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

/**
 * A queue of middleware.
 *
 * @psalm-suppress MixedPropertyTypeCoercion
 */
final class MiddlewareQueue implements MiddlewareQueueInterface
{
    /**
     * @var DataStructure\PriorityQueue<MiddlewareInterface>
     */
    private DataStructure\PriorityQueue $queue;

    public function __construct()
    {
        $this->queue = new DataStructure\PriorityQueue();
    }

    /**
     * @inheritDoc
     */
    public function enqueue(MiddlewareInterface $middleware, int $priority = 0): void
    {
        if ($middleware instanceof PrioritizedMiddlewareInterface) {
            $priority = $middleware->getPriority();
        }

        $this->queue->enqueue($middleware, $priority);
    }

    /**
     * @inheritDoc
     */
    public function wrap(HandlerInterface $handler): HandlerInterface
    {
        $queue = clone $this->queue;
        while ($middleware = $queue->pull()) {
            $handler = new MiddlewareHandler($middleware, $handler);
        }

        return $handler;
    }
}
