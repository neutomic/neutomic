<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Handler;

use Closure;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;

final readonly class ClosureHandler implements HandlerInterface
{
    private Closure $closure;

    /**
     * @param (Closure(RequestInterface): ResponseInterface) $closure
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return ($this->closure)($context, $request);
    }
}
