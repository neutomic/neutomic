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

interface BuilderInterface
{
    /**
     * Creates a conjunction of the given expressions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     * @param non-empty-string|CompositeExpressionInterface ...$expressions
     */
    public function and(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface;

    /**
     * Creates a disjunction of the given expressions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     * @param non-empty-string|CompositeExpressionInterface ...$expressions
     */
    public function or(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface;

    /**
     * Creates a comparison expression.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function comparison(string $left, Operator $operator, string $right): string;

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function equal(string $left, string $right): string;

    /**
     * Creates a non equality comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function notEqual(string $left, string $right): string;

    /**
     * Creates a lower-than comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function lowerThan(string $left, string $right): string;

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function lowerThanOrEqual(string $left, string $right): string;

    /**
     * Creates a greater-than comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function greaterThan(string $left, string $right): string;

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    public function greaterThanOrEqual(string $left, string $right): string;

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param non-empty-string $expression The expression to be restricted by IS NULL.
     *
     * @return non-empty-string
     */
    public function isNull(string $expression): string;

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param non-empty-string $expression The expression to be restricted by IS NOT NULL.
     *
     * @return non-empty-string
     */
    public function isNotNull(string $expression): string;

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     *
     * @param non-empty-string $expression The expression to be inspected by the LIKE comparison
     * @param non-empty-string $pattern The pattern to compare against
     * @param non-empty-string|null $escapeCharacters
     *
     * @return non-empty-string
     */
    public function like(string $expression, string $pattern, null|string $escapeCharacters = null): string;

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     *
     * @param non-empty-string $expression The expression to be inspected by the NOT LIKE comparison
     * @param non-empty-string $pattern The pattern to compare against
     * @param non-empty-string|null $escapeCharacters
     *
     * @return non-empty-string
     */
    public function notLike(string $expression, string $pattern, null|string $escapeCharacters = null): string;

    /**
     * Creates an IN () comparison expression with the given arguments.
     *
     * @param non-empty-string $expression The SQL expression to be matched against the set.
     * @param non-empty-string $setExpression The first SQL expression representing the set.
     * @param non-empty-string ...$setExpressions The rest of the SQL expressions representing the set.
     *
     * @return non-empty-string
     */
    public function in(string $expression, string $setExpression, string ...$setExpressions): string;

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     *
     * @param non-empty-string $expression The SQL expression to be matched against the set.
     * @param non-empty-string $setExpression The first SQL expression representing the set.
     * @param non-empty-string ...$setExpressions The rest of the SQL expressions representing the set.
     *
     * @return non-empty-string
     */
    public function notIn(string $expression, string $setExpression, string ...$setExpressions): string;
}
