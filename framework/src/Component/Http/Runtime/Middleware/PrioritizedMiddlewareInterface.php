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
