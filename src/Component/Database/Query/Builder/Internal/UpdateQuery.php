<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Exception\LogicException;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Type;
use Neu\Component\Database\Query\UpdateQueryInterface;
use Psl\Str;

/**
 * @internal
 */
final readonly class UpdateQuery extends AbstractWhereQuery implements UpdateQueryInterface
{
    /**
     * @var list<non-empty-string>
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
    public function getType(): Type
    {
        return Type::Update;
    }

    /**
     * @inheritDoc
     */
    public function set(string $column, string $value): static
    {
        $sets = $this->sets;
        $sets[] = $this->dbal->createExpressionBuilder()->equal($column, $value);

        return new self($this->dbal, $this->table, $this->alias, $sets, $this->where);
    }

    /**
     * @inheritDoc
     */
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
