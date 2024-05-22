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
