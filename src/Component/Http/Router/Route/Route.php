<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Route;

use Attribute;
use Neu\Component\Http\Message\Method;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Route
{
    /**
     * The name of this route.
     */
    public string $name;

    /**
     * The path of this route.
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
     * @var array<string, mixed>
     */
    public array $attributes;

    /**
     * Create a new route instance.
     *
     * @param non-empty-list<Method> $methods
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
