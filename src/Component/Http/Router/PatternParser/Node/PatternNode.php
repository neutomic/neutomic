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

namespace Neu\Component\Http\Router\PatternParser\Node;

use Psl\Str;
use Psl\Vec;

/**
 * A node representing a pattern.
 *
 * @psalm-type State = array{children: list<Node>}
 */
final readonly class PatternNode implements Node
{
    /**
     * The children of the node.
     *
     * @var list<Node>
     */
    private array $children;

    /**
     * Create a new {@see PatternNode} instance.
     *
     * @param Node ...$children The children of the node.
     */
    public function __construct(Node ...$children)
    {
        $this->children = Vec\values($children);
    }

    /**
     * Get the children of the node.
     *
     * @return list<Node>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toRegularExpression(string $delimiter): string
    {
        /** @var non-empty-string */
        return Str\join(Vec\map(
            $this->children,
            static fn (Node $child): string => $child->toRegularExpression($delimiter),
        ), '');
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toString(): string
    {
        return '[' . Str\join(Vec\map(
            $this->children,
            static fn (Node $child): string => $child->toString(),
        ), ', ') . ']';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __serialize(): array
    {
        return [
            'children' => $this->children,
        ];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __unserialize(array $data): void
    {
        /** @var State $data */
        $this->children = $data['children'];
    }
}
