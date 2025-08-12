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

namespace Neu\Component\Http\Router\Registry;

use Neu\Component\Http\Exception\OutOfBoundsException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Router\PrefixMap\PrefixMap;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Utility\AlternativeFinder;
use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

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
     * The prefix maps for each HTTP method.
     *
     * @var null|array<value-of<Method>, PrefixMap>
     */
    private null|array $prefixMaps = null;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function register(Route $route, HandlerInterface $handler): void
    {
        $this->prefixMaps = null;

        $this->routes[$route->name] = $route;
        $this->handlers[$route->name] = $handler;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function incorporate(RegistryInterface $registry): void
    {
        foreach ($registry->getRoutes() as $route) {
            $this->register($route, $registry->getHandler($route->name));
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function has(string $name): bool
    {
        return Iter\contains_key($this->routes, $name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function getRoutes(): array
    {
        return Vec\sort($this->routes, static function (Route $a, Route $b): int {
            return $a->priority <=> $b->priority;
        });
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getPrefixMaps(): array
    {
        if (null === $this->prefixMaps) {
            $map = [];
            foreach ($this->getRoutes() as $route) {
                foreach ($route->methods as $method) {
                    $map[$method->value][] = $route;
                }
            }

            $this->prefixMaps = Dict\map($map, PrefixMap::fromRoutes(...));
        }

        return $this->prefixMaps;
    }

    /**
     * Builds a custom exception for missing routes or handlers.
     *
     * @param non-empty-string $name The name of the missing route or handler.
     */
    private function buildException(string $name): OutOfBoundsException
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
        } else {
            $message .= '.';
        }

        return new OutOfBoundsException($message);
    }
}
