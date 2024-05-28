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

namespace Neu\Component\Http\Router\PrefixMap;

use Neu\Component\Http\Router\Route;

/**
 * Class representing either a PrefixMap or a Route in the prefix matching process.
 */
final class PrefixMapOrRoute
{
    /**
     * @param null|PrefixMap $map
     * @param null|Route $route
     */
    private function __construct(private null|PrefixMap $map, private null|Route $route)
    {
    }

    /**
     * Create an instance from a PrefixMap.
     *
     * @param PrefixMap $map The PrefixMap instance.
     *
     * @return self
     */
    public static function fromMap(PrefixMap $map): self
    {
        return new self($map, null);
    }

    /**
     * Create an instance from a Route.
     *
     * @param Route $route The Route instance.
     *
     * @return self
     */
    public static function fromRoute(Route $route): self
    {
        return new self(null, $route);
    }

    /**
     * Check if this instance represents a PrefixMap.
     *
     * @return bool True if it is a PrefixMap, false otherwise.
     */
    public function isMap(): bool
    {
        return $this->map !== null;
    }

    /**
     * Check if this instance represents a Route.
     *
     * @return bool True if it is a Route, false otherwise.
     */
    public function isRoute(): bool
    {
        return $this->route !== null;
    }

    /**
     * Get the PrefixMap instance.
     *
     * @return PrefixMap
     */
    public function getMap(): PrefixMap
    {
        assert($this->map instanceof PrefixMap);

        /** @var PrefixMap */
        return $this->map;
    }

    /**
     * Get the Route instance.
     *
     * @return Route
     */
    public function getRoute(): Route
    {
        assert($this->route instanceof Route);

        /** @var Route */
        return $this->route;
    }

    /**
     * Serialize the instance into an array.
     *
     * @return array{map: null|PrefixMap, route: null|Route}
     */
    public function __serialize(): array
    {
        return ['map' => $this->map, 'route' => $this->route];
    }

    /**
     * Unserialize the instance from an array.
     *
     * @param array{map: null|PrefixMap, route: null|Route} $data The serialized data.
     */
    public function __unserialize(array $data): void
    {
        ['map' => $this->map, 'route' => $this->route] = $data;
    }
}
