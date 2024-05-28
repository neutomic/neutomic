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

namespace Neu\Component\Http\Router\Matcher;

use Neu\Component\Http\Router\Route;
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
     * @var array<non-empty-string, non-empty-string>
     */
    public array $parameters;

    /**
     * Create a new match result.
     *
     * @param Route $route The matched route.
     * @param HandlerInterface $handler The route handler.
     * @param array<non-empty-string, non-empty-string> $parameters The route parameters.
     */
    public function __construct(Route $route, HandlerInterface $handler, array $parameters)
    {
        $this->route = $route;
        $this->handler = $handler;
        $this->parameters = $parameters;
    }
}
