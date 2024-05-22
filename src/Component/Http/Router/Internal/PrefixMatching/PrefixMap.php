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

use Neu\Component\Http\Router\Internal\PatternParser\LiteralNode;
use Neu\Component\Http\Router\Internal\PatternParser\Node;
use Neu\Component\Http\Router\Internal\PatternParser\ParameterNode;
use Neu\Component\Http\Router\Internal\PatternParser\Parser;
use Neu\Component\Http\Router\Route\Route;
use Psl\Dict;

use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function implode;
use function min;
use function strlen;
use function substr;

/**
 * @internal
 */
final readonly class PrefixMap
{
    /**
     * @var array<string, Route>
     */
    public array $literals;

    /**
     * @var array<string, PrefixMap>
     */
    public array $prefixes;

    /**
     * @var array<string, PrefixMapOrRoute>
     */
    public array $regexps;

    public int $prefixLength;

    /**
     * @param array<string, Route> $literals
     * @param array<string, PrefixMap> $prefixes
     * @param array<string, PrefixMapOrRoute> $regexps
     */
    public function __construct(array $literals, array $prefixes, array $regexps, int $prefixLength)
    {
        $this->literals = $literals;
        $this->prefixes = $prefixes;
        $this->regexps = $regexps;
        $this->prefixLength = $prefixLength;
    }

    /**
     * Create a PrefixMap from a flat map, where the key is the path and the value is the route.
     *
     * @param list<Route> $map
     *
     * @return PrefixMap
     */
    public static function fromFlatMap(array $map): PrefixMap
    {
        $entries = array_map(
            /**
             * @param Route $route
             *
             * @return array{0: list<Node>, 1: Route}
             */
            static fn (Route $route): array => [
                Parser::parse($route->path)->children,
                $route
            ],
            $map,
        );

        return self::fromFlatMapImpl($entries);
    }

    /**
     * @param list<array{0: list<Node>, 1: Route}> $entries
     *
     * @return PrefixMap
     */
    private static function fromFlatMapImpl(array $entries): PrefixMap
    {
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
                    $regexps[] = [$node->asRegexp('#'), $nodes, $route];
                    continue;
                }
            }

            $regexps[] = [
                implode('', array_map(
                    static fn (Node $n): string => $n->asRegexp('#'),
                    array_merge([$node], $nodes),
                )),
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

        [$prefix_length, $grouped] = self::groupByCommonPrefix(array_keys($by_first));
        $prefixes = Dict\map_with_key(
            $grouped,
            /**
             * @param non-empty-string $prefix
             * @param list<non-empty-string> $keys
             */
            static function (string $prefix, array $keys) use ($by_first, $prefix_length): PrefixMap {
                return self::fromFlatMapImpl(array_merge(...array_map(
                    /**
                     * @return list<array{0: list<Node>, 1: Route}>
                     */
                    static fn (string $key) => array_map(
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
                            $suffix = substr($text, $prefix_length);
                            return [
                                array_merge([new LiteralNode($suffix)], $nodes),
                                $route,
                            ];
                        },
                        $by_first[$key],
                    ),
                    $keys,
                )));
            },
        );

        $by_first = Dict\group_by(
            $regexps,
            /**
             * @param array{0: string, 1: list<Node>, 2: Route} $entry
             */
            static fn (array $entry): string => $entry[0]
        );
        $regexps = [];
        foreach ($by_first as $first => $group_entries) {
            if (count($group_entries) === 1) {
                [, $nodes, $route] = $group_entries[0];
                $rest = implode('', array_map(static fn (Node $n): string => $n->asRegexp('#'), $nodes));
                $regexps[$first . $rest] = PrefixMapOrRoute::fromRoute($route);
                continue;
            }

            $regexps[$first] = PrefixMapOrRoute::fromMap(
                self::fromFlatMapImpl(array_map(
                    /**
                     * @param array{0: string, 1: list<Node>, 2: Route} $e
                     *
                     * @return array{0: list<Node>, 1: Route}
                     */
                    static fn (array $e): array => [$e[1], $e[2]],
                    $group_entries,
                )),
            );
        }

        return new self($literals, $prefixes, $regexps, $prefix_length);
    }

    /**
     * @param list<non-empty-string> $keys
     *
     * @return array{0: int<0, max>, 1: array<non-empty-string, list<non-empty-string>>}
     */
    private static function groupByCommonPrefix(array $keys): array
    {
        if (!$keys) {
            return [0, []];
        }

        $lens = array_map(static fn (string $key): int => strlen($key), $keys);
        $min = min($lens);

        return [$min, Dict\group_by(
            $keys,
            /**
             * @param non-empty-string $key
             *
             * @return non-empty-string
             */
            static function (string $key) use ($min): string {
                /** @var non-empty-string */
                return substr($key, 0, $min);
            },
        )];
    }

    /**
     * @return array{
     *   literals: array<string, Route>,
     *   prefixes: array<string, PrefixMap>,
     *   regexps: array<string, PrefixMapOrRoute>,
     *   prefix_length: int
     * }
     *
     * @internal
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
     * @param array{
     *   literals: array<string, Route>,
     *   prefixes: array<string, PrefixMap>,
     *   regexps: array<string, PrefixMapOrRoute>,
     *   prefix_length: int
     * } $data
     *
     * @internal
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
