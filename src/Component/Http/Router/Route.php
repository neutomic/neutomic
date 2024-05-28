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

use Attribute;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Router\PatternParser\Node\PatternNode;
use Neu\Component\Http\Router\PatternParser\Parser;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Route
{
    /**
     * The name of this route.
     *
     * @var non-empty-string
     */
    public string $name;

    /**
     * The pattern of this route.
     *
     * @var non-empty-string
     */
    public string $pattern;

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
     * The parsed pattern structure.
     *
     * @var PatternNode|null
     */
    private null|PatternNode $parsedPattern = null;

    /**
     * Create a new {@see Route} instance.
     *
     * @param non-empty-string $name The name of the route.
     * @param non-empty-string $pattern The pattern of the route.
     * @param non-empty-list<Method> $methods The methods for the route.
     * @param int $priority The priority of the route.
     * @param array<non-empty-string, mixed> $attributes The attributes for the route.
     */
    public function __construct(string $name, string $pattern, array $methods, int $priority = 0, array $attributes = [])
    {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->methods = $methods;
        $this->priority = $priority;
        $this->attributes = $attributes;
    }

    /**
     * Get the parsed pattern structure.
     *
     * @throws RuntimeException If failed to parse the pattern.
     *
     * @return PatternNode The parsed pattern structure.
     */
    public function getParsedPattern(): PatternNode
    {
        if ($this->parsedPattern === null) {
            $this->parsedPattern = Parser::parse($this->pattern);
        }

        return $this->parsedPattern;
    }
}
