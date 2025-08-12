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

namespace Neu\Framework\Middleware;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Router\Matcher\MatcherInterface;
use Neu\Component\Http\Router\Matcher\Result;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Middleware\PrioritizedMiddlewareInterface;

/**
 * A middleware that matches the request to a route.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
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

    #[\Override]
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

    #[\Override]
    public function getPriority(): int
    {
        return $this->priority;
    }
}
