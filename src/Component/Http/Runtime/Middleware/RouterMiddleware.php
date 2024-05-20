<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Matcher\Result;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final readonly class RouterMiddleware implements PrioritizedMiddlewareInterface
{
    public const int PRIORITY = 256;

    private MatcherInterface $matcher;
    private int $priority;

    public function __construct(MatcherInterface $matcher, int $priority = self::PRIORITY)
    {
        $this->matcher = $matcher;
        $this->priority = $priority;
    }

    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        $result = $this->matcher->match($request);

        foreach ($result->parameters as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $request = $request
            ->withAttribute(Result::class, $result)
            ->withAttribute(Route::class, $result->route)
            ->withAttribute(HandlerInterface::class, $result->handler)
        ;

        return $next->handle($context, $request);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
