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
use Neu\Component\Database\Exception\LogicException;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Type;
use Neu\Component\Database\Query\UpdateQueryInterface;
use Psl\Str;
use Override;

/**
 * @internal
 */
final readonly class UpdateQuery extends AbstractWhereQuery implements UpdateQueryInterface
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
     * @var list<non-empty-string>
     */
    private array $sets;

    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     * @param list<non-empty-string> $sets
     * @param null|CompositeExpressionInterface|non-empty-string $where
     */
    public function __construct(AbstractionLayerInterface $dbal, string $table, null|string $alias = null, array $sets = [], null|string|CompositeExpressionInterface $where = null)
    {
        parent::__construct($dbal, $where);

        $this->table = $table;
        $this->alias = $alias;
        $this->sets = $sets;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getType(): Type
    {
        return Type::Update;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function set(string $column, string $value): static
    {
        $sets = $this->sets;
        $sets[] = $this->dbal->createExpressionBuilder()->equal($column, $value);

        return new self($this->dbal, $this->table, $this->alias, $sets, $this->where);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function where(CompositeExpressionInterface|string $expression): static
    {
        return new self($this->dbal, $this->table, $this->alias, $this->sets, $expression);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if ($this->sets === []) {
            throw new LogicException('UpdateQueryInterface::set() must be called at least once before attempting to execute the query.');
        }

        return 'UPDATE ' . $this->getTableSQL($this->table, $this->alias) . ' SET ' . Str\join($this->sets, ', ') . $this->getWhereSQL();
    }
}
