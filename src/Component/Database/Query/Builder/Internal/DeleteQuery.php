<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Query\DeleteQueryInterface;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Type;

/**
 * @internal
 */
final readonly class DeleteQuery extends AbstractWhereQuery implements DeleteQueryInterface
{
    private string $table;
    private null|string $alias;

    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     */
    public function __construct(AbstractionLayerInterface $dbal, string $table, null|string $alias = null, CompositeExpressionInterface|string $where = null)
    {
        parent::__construct($dbal, $where);

        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @inheritDoc
     */
    public function getType(): Type
    {
        return Type::Delete;
    }

    /**
     * @inheritDoc
     */
    public function where(CompositeExpressionInterface|string $expression): static
    {
        return new self($this->dbal, $this->table, $this->alias, $expression);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return 'DELETE FROM ' . $this->getTableSQL($this->table, $this->alias) . $this->getWhereSQL();
    }
}
