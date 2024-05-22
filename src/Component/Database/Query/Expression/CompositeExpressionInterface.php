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

namespace Neu\Component\Database\Query\Expression;

use Countable;
use Stringable;

interface CompositeExpressionInterface extends Countable, Stringable
{
    public function getType(): CompositionType;

    /**
     * Returns a new CompositeExpression with the given expressions added.
     *
     * @param non-empty-string|CompositeExpression $expression
     * @param non-empty-string|CompositeExpression ...$expressions
     */
    public function with(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): static;

    /**
     * Retrieves the string representation of this composite expression.
     *
     * @return non-empty-string
     */
    public function __toString(): string;

    /**
     * Retrieves the amount of expressions on composite expression.
     */
    public function count(): int;
}
