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

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Query\Expression\CompositeExpression;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Expression\CompositionType;
use Neu\Component\Database\Query\WhereQueryInterface;

/**
 * @internal
 */
abstract readonly class AbstractWhereQuery extends AbstractExecutableQuery implements WhereQueryInterface
{
    /**
     * @var null|non-empty-string|CompositeExpressionInterface
     */
    protected null|string|CompositeExpressionInterface $where;

    /**
     * @param null|non-empty-string|CompositeExpressionInterface $where
     */
    public function __construct(AbstractionLayerInterface $dbal, null|string|CompositeExpressionInterface $where)
    {
        parent::__construct($dbal);

        $this->where = $where;
    }

    /**
     * Adds a restriction to the query results, forming a logical disjunction with any previously specified restrictions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     *
     * @see where()
     */
    #[\Override]
    public function orWhere(string|CompositeExpressionInterface $expression): static
    {
        if ($this->where === null) {
            return $this->where($expression);
        }

        $where = $this->where;
        if ($where instanceof CompositeExpressionInterface && $where->getType() === CompositionType::Disjunction) {
            $where = $where->with((string) $expression);
        } else {
            $where = CompositeExpression::or($where, $expression);
        }

        return $this->where($where);
    }

    /**
     * Adds a restriction to the query results, forming a logical conjunction with any previously specified restrictions.
     *
     * @param non-empty-string|CompositeExpressionInterface $expression
     *
     *@see where()
     */
    #[\Override]
    public function andWhere(string|CompositeExpressionInterface $expression): static
    {
        if ($this->where === null) {
            return $this->where($expression);
        }

        $where = $this->where;
        if ($where instanceof CompositeExpressionInterface && $where->getType() === CompositionType::Conjunction) {
            $where = $where->with((string) $expression);
        } else {
            $where = CompositeExpression::and($where, $expression);
        }

        return $this->where($where);
    }

    protected function getWhereSQL(): string
    {
        if ($this->where === null) {
            return '';
        }

        $where = $this->where;
        if ($where instanceof CompositeExpressionInterface) {
            $where = (string) $where;
        }

        return ' WHERE ' . $where;
    }
}
