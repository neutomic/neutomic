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

namespace Neu\Component\Http\Router\Generator;

use Neu\Component\Cache\Exception\InvalidArgumentException;
use Neu\Component\Http\Exception\OutOfBoundsException;
use Neu\Component\Http\Exception\UnexpectedValueException;
use Neu\Component\Http\Message\UriInterface;

interface GeneratorInterface
{
    /**
     * Generate a path for a given route name.
     *
     * Parameters are optional and can be used to replace placeholders in the route pattern,
     * if extra parameters are provided, they will be appended as query string.
     *
     * @param non-empty-string $name The route name.
     * @param array<non-empty-string, scalar> $parameters The route parameters.
     *
     * @throws OutOfBoundsException If the route name is not found in the registry.
     * @throws InvalidArgumentException If a required parameter is missing from the parameters array.
     * @throws UnexpectedValueException If a parameter is not of the expected type.
     *
     * @return UriInterface The generated URI.
     */
    public function generate(string $name, array $parameters = []): UriInterface;
}
