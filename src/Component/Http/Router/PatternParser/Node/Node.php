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
 * Interface representing a node in the URI pattern parsing process.
 *
 * This interface defines the contract for nodes used within the pattern
 * parser of the router component. Implementations of this interface are
 * responsible for converting the node into a regular expression and providing
 * a string representation for debugging purposes.
 */
interface Node
{
    /**
     * Convert the node to a regular expression pattern.
     *
     * This method generates a regular expression string based on the node's
     * pattern, incorporating the specified delimiter.
     *
     * @param non-empty-string $delimiter The delimiter to use in the regular expression.
     *
     * @return string The regular expression string representing the node.
     */
    public function toRegularExpression(string $delimiter): string;

    /**
     * Get the string representation of the node.
     *
     * This method returns a string representation of the node, primarily
     * intended for debugging purposes.
     *
     * @return non-empty-string The string representation of the node.
     */
    public function toString(): string;

    /**
     * Serialize the node into an array.
     *
     * This method is responsible for converting the node's state into an array
     * for serialization purposes. This is useful for caching the node's state.
     *
     * @return array<non-empty-string, mixed> An array representation of the node's state.
     */
    public function __serialize(): array;

    /**
     * Unserialize the node from an array.
     *
     * This method is responsible for restoring the node's state from an array
     * that was previously serialized. This is useful for retrieving the node's
     * state from a cache.
     *
     * @param array<non-empty-string, mixed> $data An array representation of the node's state.
     */
    public function __unserialize(array $data): void;
}
