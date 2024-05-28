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

use Neu\Component\Http\Exception\InvalidArgumentException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Exception\UnexpectedValueException;
use Neu\Component\Http\Message\Uri;
use Neu\Component\Http\Message\UriInterface;
use Neu\Component\Http\Router\PatternParser\Node\LiteralNode;
use Neu\Component\Http\Router\PatternParser\Node\Node;
use Neu\Component\Http\Router\PatternParser\Node\OptionalNode;
use Neu\Component\Http\Router\PatternParser\Node\ParameterNode;
use Neu\Component\Http\Router\Registry\RegistryInterface;
use Psl\Iter;
use Psl\Regex;
use Psl\Str;

use function rawurlencode;

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
    public function generate(string $name, array $parameters = []): UriInterface
    {
        $route = $this->registry->getRoute($name);
        $nodes = $route->getParsedPattern()->getChildren();
        $segments = $this->mapNodes($name, $nodes, $parameters);

        return Uri::fromUrl(Str\join($segments, ''));
    }

    /**
     * Map the nodes to a path.
     *
     * @param non-empty-string $name The route name.
     * @param list<Node> $nodes The nodes to map.
     * @param array<non-empty-string, scalar> $parameters The parameters to use.
     *
     * @throws InvalidArgumentException If a required parameter is missing.
     * @throws UnexpectedValueException If a parameter does not match the expected pattern.
     *
     * @return list<string> The mapped path segments.
     */
    private function mapNodes(string $name, array $nodes, array $parameters): array
    {
        $segments = [];
        foreach ($nodes as $node) {
            if ($node instanceof LiteralNode) {
                $segments[] = $node->getText();

                continue;
            }

            if ($node instanceof ParameterNode) {
                $regex = $node->getRegexp();
                $parameter = $node->getName();
                if (!Iter\contains_key($parameters, $parameter)) {
                    throw new InvalidArgumentException(
                        message: 'Missing required parameter "' . $parameter . '" for route "' . $name . '".',
                    );
                }

                $value = (string) $parameters[$parameter];
                if ($regex !== null && !Regex\matches($value, '/' . $regex . '/')) {
                    throw new UnexpectedValueException(
                        message: 'Parameter "' . $parameter . '" for route "' . $name . '" does not match the expected pattern "' . $regex . '".',
                    );
                }

                $segments[] = rawurlencode($value);

                continue;
            }

            if ($node instanceof OptionalNode) {
                $children = $node->getPattern()->getChildren();

                try {
                    $segments = [...$segments, ...$this->mapNodes($name, $children, $parameters)];
                } catch (InvalidArgumentException) {
                    // Ignore missing optional parameters.
                }

                continue;
            }

            throw new RuntimeException(
                message: 'Unsupported node type "' . $node::class . '".',
            );
        }

        return $segments;
    }
}
