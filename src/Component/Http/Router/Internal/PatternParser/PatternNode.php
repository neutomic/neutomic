<?php

declare(strict_types=1);

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
            static fn(Node $child): string => $child->toStringForDebug(),
            $this->children,
        )) . ']';
    }

    public function asRegexp(string $delimiter): string
    {
        return implode('', array_map(
            static fn(Node $child): string => $child->asRegexp($delimiter),
            $this->children,
        ));
    }

    /**
     * @return array{children: non-empty-list<Node>}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return ['children' => $this->children];
    }

    /**
     * @param array{children: non-empty-list<Node>} $data
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        $this->children = $data['children'];
    }
}
