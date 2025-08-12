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
use Neu\Component\Database\Query\DeleteQueryInterface;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Type;
use Override;

/**
 * @internal
 */
final readonly class DeleteQuery extends AbstractWhereQuery implements DeleteQueryInterface
{
    /**
     * @var non-empty-string
     */
    private string $table;

    /**
     * @var null|non-empty-string
     */
    private null|string $alias;

    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     * @param null|CompositeExpressionInterface|non-empty-string $where
     */
    public function __construct(AbstractionLayerInterface $dbal, string $table, null|string $alias = null, CompositeExpressionInterface|string|null $where = null)
    {
        parent::__construct($dbal, $where);

        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getType(): Type
    {
        return Type::Delete;
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
