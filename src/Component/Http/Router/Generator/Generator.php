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

use Neu\Component\Http\Exception\RouteNotFoundException;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;

final readonly class Generator implements GeneratorInterface
{
    private RegistryInterface $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function generate(string $name, array $parameters = []): never
    {
        $route = $this->registry->getRoute($name);

        throw new RouteNotFoundException('TODO: Implement generate() method, route: ' . $route->name . ', parameters: ' . ((string) json_encode($parameters)) . '.');
    }
}
