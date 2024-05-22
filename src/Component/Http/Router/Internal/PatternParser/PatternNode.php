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

namespace Neu\Component\Http\Router\Internal\PatternParser;

use function array_map;
use function implode;

/**
 * @internal
 */
final readonly class PatternNode implements Node
{
    /**
     * @param list<Node> $children
     */
    public function __construct(public array $children)
    {
    }

    public function toStringForDebug(): string
    {
        return '[' . implode(', ', array_map(
            static fn (Node $child): string => $child->toStringForDebug(),
            $this->children,
        )) . ']';
    }

    public function asRegexp(string $delimiter): string
    {
        return implode('', array_map(
            static fn (Node $child): string => $child->asRegexp($delimiter),
            $this->children,
        ));
    }

    /**
     * @return array{children: list<Node>}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['children' => $this->children];
    }

    /**
     * @param array{children: list<Node>} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        $this->children = $data['children'];
    }
}
