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

namespace Neu\Component\Http\Router;

use Closure;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Router\Registry\PrefixedRegistry;
use Neu\Component\Http\Router\Registry\RegistryInterface;
use Neu\Component\Http\Runtime\Handler\ClosureHandler;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * @psalm-import-type Handler from ClosureHandler
 */
final readonly class RouteCollector
{
    /**
     * The registry of routes.
     *
     * @var RegistryInterface
     */
    private RegistryInterface $registry;

    /**
     * Create a new {@see RouteCollector} instance.
     *
     * @param RegistryInterface $registry The registry of routes.
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Register a route.
     *
     * @param Route $route The route to register.
     * @param HandlerInterface $handler The handler of the route.
     */
    public function addRoute(Route $route, HandlerInterface $handler): void
    {
        $this->registry->register($route, $handler);
    }

    /**
     * Add a route.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Method|non-empty-list<Method> $methods The methods for the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function route(string $name, string $pattern, Method|array $methods, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        if (!$handler instanceof HandlerInterface) {
            $handler = new ClosureHandler($handler);
        }

        if ($methods instanceof Method) {
            $methods = [$methods];
        }

        $route = new Route($name, $pattern, $methods, $priority, $attributes);

        $this->addRoute($route, $handler);
    }

    /**
     * Add a route that matches GET requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function get(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Get, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches POST requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function post(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Post, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches PUT requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function put(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Put, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches PATCH requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function patch(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Patch, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches DELETE requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function delete(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Delete, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches HEAD requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function head(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Head, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches OPTIONS requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function options(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Options, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches TRACE requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function purge(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Purge, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches TRACE requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function trace(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Trace, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches CONNECT requests.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function connect(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::Connect, $handler, $priority, $attributes);
    }

    /**
     * Add a route that matches any request method.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param Handler|HandlerInterface $handler The handler of the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes of the route.
     */
    public function any(string $name, string $pattern, Closure|HandlerInterface $handler, int $priority = 0, array $attributes = []): void
    {
        $this->route($name, $pattern, Method::cases(), $handler, $priority, $attributes);
    }

    /**
     * Add a group of routes with a common prefix.
     *
     * @param string $prefix The common prefix of the group.
     * @param (Closure(RouteCollector): void) $callback The callback to define the routes.
     */
    public function prefix(string $prefix, callable $callback): void
    {
        $registry = new PrefixedRegistry($prefix, $this->registry);
        $collector = new self($registry);

        try {
            $callback($collector);
        } finally {
            $this->registry->incorporate($registry);
        }
    }
}
