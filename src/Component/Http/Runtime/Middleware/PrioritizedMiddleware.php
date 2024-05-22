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

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final readonly class PrioritizedMiddleware implements PrioritizedMiddlewareInterface
{
    private MiddlewareInterface $middleware;

    private int $priority;

    public function __construct(MiddlewareInterface $middleware, int $priority)
    {
        $this->middleware = $middleware;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        return $this->middleware->process($context, $request, $next);
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
