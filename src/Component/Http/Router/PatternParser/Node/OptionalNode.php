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

/**
 * A node that represents an optional pattern.
 *
 * @psalm-type State = array{pattern: PatternNode}
 */
final readonly class OptionalNode implements Node
{
    /**
     * The pattern that is optional.
     */
    private PatternNode $pattern;

    /**
     * Create a new {@see OptionalNode} instance.
     *
     * @param PatternNode $pattern The pattern that is optional.
     */
    public function __construct(PatternNode $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Get the pattern that is optional.
     */
    public function getPattern(): PatternNode
    {
        return $this->pattern;
    }

    /**
     * @inheritDoc
     */
    public function toRegularExpression(string $delimiter): string
    {
        return '(?:' . $this->pattern->toRegularExpression($delimiter) . ')?';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '?' . $this->pattern->toString();
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
        /** @var State $data */
        $this->pattern = $data['pattern'];
    }

    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [
            'pattern' => $this->pattern,
        ];
    }
}
