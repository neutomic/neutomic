<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Handler\Resolver;

use Neu\Component\Http\Exception\HandlerNotFoundException;
use Neu\Component\Http\Exception\InvalidHandlerException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final readonly class HandlerResolver implements HandlerResolverInterface
{
    private ?HandlerInterface $fallback;

    public function __construct(?HandlerInterface $fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function resolve(RequestInterface $request): HandlerInterface
    {
        if (!$request->hasAttribute(HandlerInterface::class)) {
            if ($this->fallback instanceof HandlerInterface) {
                return $this->fallback;
            }

            throw HandlerNotFoundException::forRequest($request);
        }

        $handler = $request->getAttribute(HandlerInterface::class);
        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        throw InvalidHandlerException::forHandler($handler);
    }
}
