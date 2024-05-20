<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Generator;

use Neu\Component\Http\Exception\RouteNotFoundException;
use Neu\Component\Http\Message\UriInterface;

interface GeneratorInterface
{
    /**
     * Generate a path for a given route name.
     *
     * Parameters are optional and can be used to replace placeholders in the route pattern,
     * if extra parameters are provided, they will be appended as query string.
     *
     * @param non-empty-string $name
     * @param array<string, mixed> $parameters
     *
     * @throws RouteNotFoundException If the route name is not found.
     */
    public function generate(string $name, array $parameters = []): UriInterface;
}
