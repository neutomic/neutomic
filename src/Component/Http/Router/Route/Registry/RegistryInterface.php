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

/**
 * Defines the interface for a route registry.
 *
 * This interface provides methods for registering routes and their handlers,
 * checking their existence, and retrieving them. It also allows incorporating
 * another registry's entries into this one.
 */
interface RegistryInterface
{
    /**
     * Registers a route and its associated handler.
     *
     * @param Route $route The route to register.
     * @param HandlerInterface $handler The handler associated with the route.
     */
    public function register(Route $route, HandlerInterface $handler): void;

    /**
     * Incorporates the entries from another registry into this one.
     *
     * This method integrates all routes and handlers from the specified registry
     * into the current registry, without creating a new instance. The source registry
     * remains unmodified.
     *
     * @param RegistryInterface $registry The registry whose entries are to be incorporated.
     */
    public function incorporate(RegistryInterface $registry): void;

    /**
     * Checks if a route with the specified name exists.
     *
     * @param string $name The name of the route to check.
     *
     * @return bool Returns true if the route exists, otherwise false.
     */
    public function has(string $name): bool;

    /**
     * Retrieves a route by its name.
     *
     * @param non-empty-string $name The name of the route to retrieve.
     *
     * @throws RouteNotFoundException If no route with the given name exists.
     *
     * @return Route Returns the requested route.
     */
    public function getRoute(string $name): Route;

    /**
     * Retrieves a handler by its name.
     *
     * @param non-empty-string $name The name of the handler to retrieve.
     *
     * @throws RouteNotFoundException If no handler for the given name exists.
     *
     * @return HandlerInterface Returns the requested handler.
     */
    public function getHandler(string $name): HandlerInterface;

    /**
     * Retrieves all routes registered in the registry.
     *
     * @return list<Route> A list of all registered routes.
     */
    public function getRoutes(): array;

    /**
     * Retrieves the hash of the registry.
     *
     * The hash is used to determine if the registry has changed, and if it needs to be recompiled.
     *
     * @return non-empty-string The hash of the registry.
     */
    public function getHash(): string;
}
