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

namespace Neu\Component\Http\Router\Route;

use Attribute;
use Neu\Component\Http\Message\Method;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Route
{
    /**
     * The name of this route.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The path of this route.
     *
     * @var non-empty-string
     */
    public string $path;

    /**
     * The methods for this route.
     *
     * @var non-empty-list<Method>
     */
    public array $methods;

    /**
     * The priority of this route.
     *
     * @var int
     */
    public int $priority;

    /**
     * Optional attributes for this route.
     *
     * @var array<non-empty-string, mixed>
     */
    public array $attributes;

    /**
     * Create a new route instance.
     *
     * @param non-empty-string $name The name of this route.
     * @param non-empty-string $path The path of this route.
     * @param non-empty-list<Method> $methods The methods for this route.
     * @param int $priority The priority of this route.
     * @param array<non-empty-string, mixed> $attributes Optional attributes for this route.
     */
    public function __construct(string $name, string $path, array $methods, int $priority = 0, array $attributes = [])
    {
        $this->name = $name;
        $this->path = $path;
        $this->methods = $methods;
        $this->priority = $priority;
        $this->attributes = $attributes;
    }
}
