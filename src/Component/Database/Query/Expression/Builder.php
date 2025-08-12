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

use Psl\Str;
use Psl\Vec;
use Override;

final readonly class Builder implements BuilderInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function and(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface
    {
        return CompositeExpression::and($expression, ...$expressions);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function or(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface
    {
        return CompositeExpression::or($expression, ...$expressions);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function comparison(string $left, Operator $operator, string $right): string
    {
        return $this->rawComparison($left, $operator->value, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function equal(string $left, string $right): string
    {
        return $this->comparison($left, Operator::Equal, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function notEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::NotEqual, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function lowerThan(string $left, string $right): string
    {
        return $this->comparison($left, Operator::LowerThan, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function lowerThanOrEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::LowerThanOrEqual, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function greaterThan(string $left, string $right): string
    {
        return $this->comparison($left, Operator::GreaterThan, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function greaterThanOrEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::GreaterThanOrEqual, $right);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isNull(string $expression): string
    {
        return $expression . ' IS NULL';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isNotNull(string $expression): string
    {
        return $expression . ' IS NOT NULL';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function like(string $expression, string $pattern, null|string $escapeCharacters = null): string
    {
        $comparison = $this->rawComparison($expression, 'LIKE', $pattern);
        if (null === $escapeCharacters) {
            return $comparison;
        }

        return $comparison . ' ESCAPE ' . $escapeCharacters;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function notLike(string $expression, string $pattern, null|string $escapeCharacters = null): string
    {
        $comparison = $this->rawComparison($expression, 'NOT LIKE', $pattern);
        if (null === $escapeCharacters) {
            return $comparison;
        }

        return $comparison . ' ESCAPE ' . $escapeCharacters;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function in(string $expression, string $setExpression, string ...$setExpressions): string
    {
        return $this->rawComparison($expression, 'IN', '(' . Str\join(Vec\concat([$setExpression], $setExpressions), ', ') . ')');
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function notIn(string $expression, string $setExpression, string ...$setExpressions): string
    {
        return $this->rawComparison($expression, 'NOT IN', '(' . Str\join(Vec\concat([$setExpression], $setExpressions), ', ') . ')');
    }

    /**
     * Creates a comparison expression.
     *
     * @param non-empty-string $left The left expression.
     * @param non-empty-string $operator The operator
     * @param non-empty-string $right The right expression.
     *
     * @return non-empty-string
     */
    private function rawComparison(string $left, string $operator, string $right): string
    {
        return $left . ' ' . $operator . ' ' . $right;
    }
}
