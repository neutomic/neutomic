<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query;

use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;

interface WhereQueryInterface extends QueryInterface
{
    /**
     * Specifies a restriction to the query result.
     *
     * Any previously specified restrictions, if any, will be replaced.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     */
    public function where(string|CompositeExpressionInterface $expression): static;

    /**
     * Adds a restriction to the query results, forming a logical disjunction with any previously specified restrictions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     *
     * @see WhereQueryInterface::where()
     */
    public function orWhere(string|CompositeExpressionInterface $expression): static;

    /**
     * Adds a restriction to the query results, forming a logical conjunction with any previously specified restrictions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     *
     * @see WhereQueryInterface::where()
     */
    public function andWhere(string|CompositeExpressionInterface $expression): static;
}
