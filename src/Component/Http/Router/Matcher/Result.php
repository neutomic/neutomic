<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Matcher;

use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final readonly class Result
{
    /**
     * The matched route.
     *
     * @var Route
     */
    public Route $route;

    /**
     * The route handler.
     *
     * @var HandlerInterface
     */
    public HandlerInterface $handler;

    /**
     * The route parameters.
     *
     * @var array<string, scalar>
     */
    public array $parameters;

    /**
     * Create a new match result.
     *
     * @param Route $route The matched route.
     * @param HandlerInterface $handler The route handler.
     * @param array<string, scalar> $parameters The route parameters.
     */
    public function __construct(Route $route, HandlerInterface $handler, array $parameters)
    {
        $this->route = $route;
        $this->handler = $handler;
        $this->parameters = $parameters;
    }
}
