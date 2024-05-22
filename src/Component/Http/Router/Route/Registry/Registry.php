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

namespace Neu\Component\Http\Router\Route\Registry;

use Neu\Component\Http\Exception\RouteNotFoundException;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Utility\AlternativeFinder;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function md5;
use function serialize;

final class Registry implements RegistryInterface
{
    /**
     * An associative array storing routes keyed by their name.
     *
     * @var array<non-empty-string, Route>
     */
    private array $routes = [];

    /**
     * An associative array storing handlers keyed by route name.
     *
     * @var array<non-empty-string, HandlerInterface>
     */
    private array $handlers = [];

    /**
     * @inheritDoc
     */
    public function register(Route $route, HandlerInterface $handler): void
    {
        $this->routes[$route->name] = $route;
        $this->handlers[$route->name] = $handler;
    }

    /**
     * @inheritDoc
     */
    public function incorporate(RegistryInterface $registry): void
    {
        foreach ($registry->getRoutes() as $route) {
            $this->register($route, $registry->getHandler($route->name));
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return Iter\contains_key($this->routes, $name);
    }

    /**
     * @inheritDoc
     */
    public function getRoute(string $name): Route
    {
        if (Iter\contains_key($this->routes, $name)) {
            return $this->routes[$name];
        }

        throw $this->buildException($name);
    }

    /**
     * @inheritDoc
     */
    public function getHandler(string $name): HandlerInterface
    {
        if (Iter\contains_key($this->handlers, $name)) {
            return $this->handlers[$name];
        }

        throw $this->buildException($name);
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array
    {
        return Vec\sort($this->routes, static function (Route $a, Route $b): int {
            return $a->priority <=> $b->priority;
        });
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return md5(serialize($this->routes));
    }

    /**
     * Builds a custom exception for missing routes or handlers.
     *
     * @param non-empty-string $name The name of the missing route or handler.
     */
    private function buildException(string $name): RouteNotFoundException
    {
        $allNames = Vec\keys($this->routes);
        $message = Str\format('Route "%s" was not found', $name);
        $alternatives = AlternativeFinder::findAlternatives($name, $allNames);

        if (!Iter\is_empty($alternatives)) {
            if (Iter\count($alternatives) === 1) {
                $message .= Str\format(', did you mean "%s"?', $alternatives[0]);
            } else {
                $message .= Str\format(', did you mean one of the following: "%s"?', Str\join($alternatives, '", "'));
            }
        }

        return new RouteNotFoundException($message);
    }
}
