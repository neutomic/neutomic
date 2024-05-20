<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Expression;

use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final readonly class CompositeExpression implements CompositeExpressionInterface
{
    /**
     * The type of composition.
     *
     * @var CompositionType
     */
    private CompositionType $type;

    /**
     * The expressions to be composed.
     *
     * @var list<non-empty-string|CompositeExpressionInterface>
     */
    private array $expressions;

    /**
     * @param list<non-empty-string|CompositeExpressionInterface> $expressions
     */
    private function __construct(CompositionType $type, array $expressions)
    {
        $this->type = $type;
        $this->expressions = $expressions;
    }

    /**
     * @param non-empty-string|CompositeExpressionInterface $expression
     * @param non-empty-string|CompositeExpressionInterface ...$expressions
     */
    public static function and(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): static
    {
        return new self(CompositionType::Conjunction, Vec\concat([$expression], $expressions));
    }

    /**
     * @param non-empty-string|CompositeExpressionInterface $expression
     * @param non-empty-string|CompositeExpressionInterface ...$expressions
     */
    public static function or(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): static
    {
        return new self(CompositionType::Disjunction, Vec\concat([$expression], $expressions));
    }

    public function getType(): CompositionType
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function with(string|CompositeExpressionInterface $expression, string|CompositeExpressionInterface ...$expressions): static
    {
        $parts = Vec\concat($this->expressions, [$expression], $expressions);

        return new self($this->type, $parts);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if ($this->count() === 1) {
            return (string) $this->expressions[0];
        }

        $expressions = Vec\map($this->expressions, static fn(string|CompositeExpressionInterface $expression): string => (string) $expression);

        return '(' . Str\join($expressions, ') ' . $this->type->value . ' (') . ')';
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return Iter\count($this->expressions);
    }
}
