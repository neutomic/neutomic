<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Middleware;

/**
 * Interface for middleware that can be prioritized.
 */
interface PrioritizedMiddlewareInterface extends MiddlewareInterface
{
    /**
     * Returns the priority of the middleware.
     *
     * Middlewares with higher priority are executed first.
     *
     * @return int The middleware priority
     */
    public function getPriority(): int;
}
