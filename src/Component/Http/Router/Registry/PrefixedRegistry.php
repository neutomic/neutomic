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

use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Psl\Str;

/**
 * A registry that adds a prefix to all routes.
 */
final readonly class PrefixedRegistry implements RegistryInterface
{
    /**
     * The prefix of the registry.
     *
     * @var string
     */
    private string $prefix;

    /**
     * The internal registry to add prefixed routes to.
     *
     * @var RegistryInterface
     */
    private RegistryInterface $registry;

    /**
     * Create a new {@see PrefixedRegistry} instance.
     *
     * @param string $prefix The prefix of the registry.
     * @param RegistryInterface $registry The registry to add prefixed routes to.
     */
    public function __construct(string $prefix, RegistryInterface $registry)
    {
        $this->prefix = Str\Byte\trim_right($prefix, '/');
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function register(Route $route, HandlerInterface $handler): void
    {
        $pattern = $this->prefix . '/' . Str\Byte\trim_left($route->pattern, '/');

        $this->registry->register(new Route($route->name, $pattern, $route->methods, $route->priority, $route->attributes), $handler);
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
        return $this->registry->has($name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getRoute(string $name): Route
    {
        return $this->registry->getRoute($name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getHandler(string $name): HandlerInterface
    {
        return $this->registry->getHandler($name);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getRoutes(): array
    {
        return $this->registry->getRoutes();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getPrefixMaps(): array
    {
        return $this->registry->getPrefixMaps();
    }
}
