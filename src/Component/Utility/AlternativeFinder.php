<?php

declare(strict_types=1);

namespace Neu\Component\Utility;

use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

enum AlternativeFinder
{
    private const float THRESHOLD = 1e3;

    /**
     * Finds alternatives for a given name among a collection of strings.
     *
     * @param non-empty-string $name The name to find alternatives for.
     * @param list<non-empty-string> $collection The collection of strings to search within.
     * @param non-empty-string $delimiter The delimiter used to split the strings into parts.
     *
     * @return list<non-empty-string> A list of alternative strings.
     */
    public static function findAlternatives(string $name, array $collection, string $delimiter = ':'): array
    {
        $alternatives = [];
        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = Str\split($item, $delimiter);
        }

        foreach (Str\split($name, $delimiter) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = Iter\contains_key($alternatives, $collectionName);
                if (!Iter\contains_key($parts, $i)) {
                    if ($exists) {
                        $alternatives[$collectionName] += self::THRESHOLD;
                    }

                    continue;
                }

                $lev = (float)Str\levenshtein($subname, $parts[$i]);
                if ($lev <= Str\length($subname) / 3 || ('' !== $subname && Str\contains($parts[$i], $subname))) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += self::THRESHOLD;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = (float)Str\levenshtein($name, $item);
            if ($lev <= Str\length($name) / 3 || Str\contains($item, $name)) {
                $alternatives[$item] = Iter\contains_key($alternatives, $item) ? $alternatives[$item] - $lev : $lev;
            }
        }

        return Vec\keys(Dict\sort(
            Dict\filter($alternatives, static fn(int $lev): bool => $lev < (2 * self::THRESHOLD)),
        ));
    }
}
