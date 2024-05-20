<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Handler;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Middleware\MiddlewareInterface;

final readonly class MiddlewareHandler implements HandlerInterface
{
    public function __construct(
        private MiddlewareInterface $middleware,
        private HandlerInterface $next
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($context, $request, $this->next);
    }
}
