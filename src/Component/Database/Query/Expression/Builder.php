<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Expression;

use Psl\Str;
use Psl\Vec;

final readonly class Builder implements BuilderInterface
{
    /**
     * @inheritDoc
     */
    public function and(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface
    {
        return CompositeExpression::and($expression, ...$expressions);
    }

    /**
     * @inheritDoc
     */
    public function or(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): CompositeExpressionInterface
    {
        return CompositeExpression::or($expression, ...$expressions);
    }

    /**
     * @inheritDoc
     */
    public function comparison(string $left, Operator $operator, string $right): string
    {
        return $this->rawComparison($left, $operator->value, $right);
    }

    /**
     * @inheritDoc
     */
    public function equal(string $left, string $right): string
    {
        return $this->comparison($left, Operator::Equal, $right);
    }

    /**
     * @inheritDoc
     */
    public function notEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::NotEqual, $right);
    }

    /**
     * @inheritDoc
     */
    public function lowerThan(string $left, string $right): string
    {
        return $this->comparison($left, Operator::LowerThan, $right);
    }

    /**
     * @inheritDoc
     */
    public function lowerThanOrEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::LowerThanOrEqual, $right);
    }

    /**
     * @inheritDoc
     */
    public function greaterThan(string $left, string $right): string
    {
        return $this->comparison($left, Operator::GreaterThan, $right);
    }

    /**
     * @inheritDoc
     */
    public function greaterThanOrEqual(string $left, string $right): string
    {
        return $this->comparison($left, Operator::GreaterThanOrEqual, $right);
    }

    /**
     * @inheritDoc
     */
    public function isNull(string $expression): string
    {
        return $expression . ' IS NULL';
    }

    /**
     * @inheritDoc
     */
    public function isNotNull(string $expression): string
    {
        return $expression . ' IS NOT NULL';
    }

    /**
     * @inheritDoc
     */
    public function like(string $expression, string $pattern, ?string $escapeCharacters = null): string
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
    public function notLike(string $expression, string $pattern, ?string $escapeCharacters = null): string
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
    public function in(string $expression, string $setExpression, string ...$setExpressions): string
    {
        return $this->rawComparison($expression, 'IN', '(' . Str\join(Vec\concat([$setExpression], $setExpressions), ', ') . ')');
    }

    /**
     * @inheritDoc
     */
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
