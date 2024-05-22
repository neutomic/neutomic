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

namespace Neu\Component\Http\Router\Internal\PrefixMatching;

use Neu\Component\Http\Router\Route\Route;

/**
 * @internal
 */
final class PrefixMapOrRoute
{
    /**
     * @param null|PrefixMap $map
     * @param null|Route $route
     */
    private function __construct(private null|PrefixMap $map, private mixed $route)
    {
    }

    public static function fromMap(PrefixMap $map): self
    {
        return new self($map, null);
    }

    public static function fromRoute(Route $route): self
    {
        return new self(null, $route);
    }

    public function isMap(): bool
    {
        return $this->map !== null;
    }

    public function isRoute(): bool
    {
        return $this->route !== null;
    }

    public function getMap(): PrefixMap
    {
        assert($this->map instanceof PrefixMap);

        /** @var PrefixMap */
        return $this->map;
    }

    public function getRoute(): Route
    {
        assert($this->route instanceof Route);

        /** @return Route */
        return $this->route;
    }

    /**
     * @return array{map: null|PrefixMap, route: null|Route}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['map' => $this->map, 'route' => $this->route];
    }

    /**
     * @param array{map: null|PrefixMap, route: null|Route} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        ['map' => $this->map, 'route' => $this->route] = $data;
    }
}
