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

use Neu\Component\Http\Router\PatternParser\Node\LiteralNode;
use Neu\Component\Http\Router\PatternParser\Node\Node;
use Neu\Component\Http\Router\PatternParser\Node\ParameterNode;
use Neu\Component\Http\Router\Route;
use Psl\Dict;
use Psl\Vec;
use Psl\Str;
use Psl\Math;
use Psl\Str\Byte;
use Psl\Iter;

/**
 * Class representing a map of route prefixes for efficient route matching.
 *
 * @psalm-type State = array{
 *     literals: array<string, Route>,
 *     prefixes: array<string, PrefixMap>,
 *     regexps: array<string, PrefixMapOrRoute>,
 *     prefix_length: int
 * }
 */
final readonly class PrefixMap
{
    /**
     * Array of literal routes.
     *
     * @var array<string, Route>
     */
    public array $literals;

    /**
     * Array of prefix maps.
     *
     * @var array<string, PrefixMap>
     */
    public array $prefixes;

    /**
     * Array of regular expression routes.
     *
     * @var array<string, PrefixMapOrRoute>
     */
    public array $regexps;

    /**
     * The minimum length of the common prefix.
     */
    public int $prefixLength;

    /**
     * Create a new {@see PrefixMap} instance.
     *
     * @param array<string, Route> $literals Array of literal routes.
     * @param array<string, PrefixMap> $prefixes Array of prefix maps.
     * @param array<string, PrefixMapOrRoute> $regexps Array of regular expression routes.
     * @param int $prefixLength The minimum length of the common prefix.
     */
    public function __construct(array $literals, array $prefixes, array $regexps, int $prefixLength)
    {
        $this->literals = $literals;
        $this->prefixes = $prefixes;
        $this->regexps = $regexps;
        $this->prefixLength = $prefixLength;
    }

    /**
     * Create a {@see PrefixMap} from a list of routes.
     *
     * This method processes a list of routes and generates a PrefixMap that organizes the routes
     * for efficient prefix-based matching.
     *
     * @param list<Route> $routes The list of routes to be processed.
     */
    public static function fromRoutes(array $routes): PrefixMap
    {
        $entries = Vec\map(
            $routes,
            /**
             * @param Route $route
             *
             * @return array{0: list<Node>, 1: Route}
             */
            static fn (Route $route): array => [
                $route->getParsedPattern()->getChildren(),
                $route
            ],
        );

        return self::fromRoutesImpl($entries);
    }

    /**
     * Internal method to create a PrefixMap from route entries.
     *
     * This method processes route entries, organizing them into literals, prefixes, and regular expressions
     * for efficient matching.
     *
     * @param list<array{0: list<Node>, 1: Route}> $entries The route entries to be processed.
     */
    private static function fromRoutesImpl(array $entries): PrefixMap
    {
        if ([] === $entries) {
            return new self([], [], [], 0);
        }

        $literals = [];
        $prefixes = [];
        $regexps = [];
        foreach ($entries as [$nodes, $route]) {
            if (!$nodes) {
                $literals[''] = $route;
                continue;
            }

            $node = array_shift($nodes);
            if ($node instanceof LiteralNode) {
                if (!$nodes) {
                    $literals[$node->getText()] = $route;
                } else {
                    $prefixes[] = [$node->getText(), $nodes, $route];
                }

                continue;
            }

            if ($node instanceof ParameterNode && $node->getRegexp() === null) {
                $next = $nodes[0] ?? null;
                if ($next instanceof LiteralNode && $next->getText()[0] === '/') {
                    $regexps[] = [$node->toRegularExpression('#'), $nodes, $route];
                    continue;
                }
            }

            $regexps[] = [
                Str\join(Vec\map(
                    Vec\concat([$node], $nodes),
                    static fn (Node $n): string => $n->toRegularExpression('#'),
                ), ''),
                [],
                $route,
            ];
        }

        /** @var array<non-empty-string, list<array{0: non-empty-string, 1: list<Node>, 2: Route}>> $by_first */
        $by_first = Dict\group_by(
            $prefixes,
            /**
             * @param array{0: non-empty-string, 1: list<Node>, 2: Route} $entry
             */
            static fn (array $entry): string => $entry[0]
        );

        [$prefix_length, $grouped] = self::groupByCommonPrefix(Vec\keys($by_first));
        $prefixes = Dict\map_with_key(
            $grouped,
            /**
             * @param non-empty-string $prefix
             * @param list<non-empty-string> $keys
             */
            static function (string $prefix, array $keys) use ($by_first, $prefix_length): PrefixMap {
                $entries = Vec\map(
                    $keys,
                    /**
                     * @return list<array{0: list<Node>, 1: Route}>
                     */
                    static fn (string $key) => Vec\map(
                        $by_first[$key],
                        /**
                         * @param array{0: non-empty-string, 1: list<Node>, 2: Route} $row
                         *
                         * @return array{0: list<Node>, 1: Route}
                         */
                        static function (array $row) use ($prefix, $prefix_length): array {
                            [$text, $nodes, $route] = $row;
                            if ($text === $prefix) {
                                return [$nodes, $route];
                            }

                            /** @var non-empty-string $suffix */
                            $suffix = Byte\slice($text, $prefix_length);
                            return [
                                Vec\concat([new LiteralNode($suffix)], $nodes),
                                $route,
                            ];
                        },
                    ),
                );

                if (1 === Iter\count($entries)) {
                    $entries = Iter\first($entries);
                } elseif ([] !== $entries) {
                    $entries = Vec\concat(...$entries);
                }

                return self::fromRoutesImpl($entries);
            },
        );

        /** @var array<string, list<array{0: string, 1: list<Node>, 2: Route}>> $by_first */
        $by_first = Dict\group_by(
            $regexps,
            /**
             * @param array{0: string, 1: list<Node>, 2: Route} $entry
             */
            static fn (array $entry): string => $entry[0]
        );
        $regexps = [];
        foreach ($by_first as $first => $group_entries) {
            if (Iter\count($group_entries) === 1) {
                [, $nodes, $route] = $group_entries[0];
                $rest = Str\join(Vec\map($nodes, static fn (Node $n): string => $n->toRegularExpression('#')), '');
                $regexps[$first . $rest] = PrefixMapOrRoute::fromRoute($route);
                continue;
            }

            $regexps[$first] = PrefixMapOrRoute::fromMap(
                self::fromRoutesImpl(Vec\map(
                    $group_entries,
                    /**
                     * @param array{0: string, 1: list<Node>, 2: Route} $e
                     *
                     * @return array{0: list<Node>, 1: Route}
                     */
                    static fn (array $e): array => [$e[1], $e[2]],
                )),
            );
        }

        return new self($literals, $prefixes, $regexps, $prefix_length);
    }

    /**
     * Group strings by their common prefix.
     *
     * This method takes a list of strings and groups them by their common prefix, returning the
     * length of the common prefix and a mapping of the common prefix to the grouped strings.
     *
     * @param list<non-empty-string> $keys The list of strings to be grouped.
     *
     * @return array{0: int<0, max>, 1: array<non-empty-string, list<non-empty-string>>} An array containing
     *                                                                                   the minimum length of the common prefix and a mapping of the common prefix to the grouped strings.
     */
    private static function groupByCommonPrefix(array $keys): array
    {
        if ([] === $keys) {
            return [0, []];
        }

        /** @var non-empty-list<int<0, max>> $lengths */
        $lengths = Vec\map($keys, Byte\length(...));
        $minimum = Math\min($lengths);

        return [$minimum, Dict\group_by(
            $keys,
            /**
             * @param non-empty-string $key
             *
             * @return non-empty-string
             */
            static function (string $key) use ($minimum): string {
                /** @var non-empty-string */
                return Byte\slice($key, 0, $minimum);
            },
        )];
    }

    /**
     * Serialize the object state to an array.
     *
     * @return State The serialized state of the object.
     */
    public function __serialize(): array
    {
        return [
            'literals' => $this->literals,
            'prefixes' => $this->prefixes,
            'regexps' => $this->regexps,
            'prefix_length' => $this->prefixLength,
        ];
    }

    /**
     * Restore the object state from an array.
     *
     * @param State $data The serialized state of the object.
     */
    public function __unserialize(array $data): void
    {
        [
            'literals' => $this->literals,
            'prefixes' => $this->prefixes,
            'regexps' => $this->regexps,
            'prefix_length' => $this->prefixLength,
        ] = $data;
    }
}
