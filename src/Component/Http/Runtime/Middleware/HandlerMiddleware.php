<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final readonly class HandlerMiddleware implements MiddlewareInterface
{
    private HandlerInterface $handler;

    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        return $this->handler->handle($context, $request);
    }
}
