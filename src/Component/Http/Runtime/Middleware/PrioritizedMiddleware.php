<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
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

    public function process(RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        return $this->middleware->process($request, $next);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
