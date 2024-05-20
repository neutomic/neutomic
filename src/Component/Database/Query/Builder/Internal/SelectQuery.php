<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Exception\LogicException;
use Neu\Component\Database\OrderDirection;
use Neu\Component\Database\Query\Expression\CompositeExpression;
use Neu\Component\Database\Query\Expression\CompositeExpressionInterface;
use Neu\Component\Database\Query\Expression\CompositionType;
use Neu\Component\Database\Query\SelectQueryInterface;
use Neu\Component\Database\Query\Type;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final readonly class SelectQuery extends AbstractWhereQuery implements SelectQueryInterface
{
    /**
     * @var non-empty-list<string>
     */
    private array $select;

    private bool $distinct;

    /**
     * @var list<array{non-empty-string, ?non-empty-string}>
     */
    private array $from;

    /**
     * @var array<non-empty-string, list<array{JoinType, non-empty-string, non-empty-string, ?non-empty-string}>>
     */
    private array $joins;

    /**
     * @var list<non-empty-string>
     */
    private array $groupBy;

    /**
     * @var non-empty-string|CompositeExpressionInterface|null
     */
    private string|CompositeExpressionInterface|null $having;

    /**
     * @var array<non-empty-string, OrderDirection>
     */
    private array $orderBy;

    /**
     * @var int<0, max>
     */
    private int $offset;

    /**
     * @var null|int<0, max>
     */
    private ?int $limit;

    /**
     * @param non-empty-list<string> $select
     * @param list<array{non-empty-string, ?non-empty-string}> $from
     * @param list<array{JoinType, non-empty-string, non-empty-string, ?non-empty-string}> $joins
     * @param list<non-empty-string> $groupBy
     * @param array<non-empty-string, OrderDirection> $orderBy
     * @param int<0, max> $offset
     * @param null|int<0, max> $limit
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        array $select,
        bool $distinct = false,
        array $from = [],
        array $joins = [],
        array $groupBy = [],
        null|string|CompositeExpressionInterface $having = null,
        array $orderBy = [],
        int $offset = 0,
        ?int $limit = null,
        null|string|CompositeExpressionInterface $where = null,
    ) {
        parent::__construct($dbal, $where);

        $this->select = $select;
        $this->distinct = $distinct;
        $this->from = $from;
        $this->joins = $joins;
        $this->groupBy = $groupBy;
        $this->having = $having;
        $this->orderBy = $orderBy;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @inheritDoc
     */
    public function getType(): Type
    {
        return Type::Select;
    }

    /**
     * @inheritDoc
     */
    public function distinct(): static
    {
        return new static(
            $this->dbal,
            $this->select,
            true,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function from(string $table, ?string $alias = null): static
    {
        $from = $this->from;
        $from[] = [$table, $alias];

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function innerJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $joins = $this->joins;
        $joins[$from][] = [JoinType::Inner, $join, $alias, $condition];

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function leftJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $joins = $this->joins;
        $joins[$from][] = [JoinType::Left, $join, $alias, $condition];

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function rightJoin(string $from, string $join, string $alias, ?string $condition = null): static
    {
        $joins = $this->joins;
        $joins[$from][] = [JoinType::Right, $join, $alias, $condition];

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function groupBy(string $expression, string ...$expressions): static
    {
        $group_by = Vec\concat([$expression], $expressions);

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $group_by,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function andGroupBy(string $expression, string ...$expressions): static
    {
        $group_by = Vec\concat($this->groupBy, [$expression], $expressions);

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $group_by,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function andHaving(CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        $previous_restriction = $this->having;
        if (null !== $previous_restriction) {
            if ($previous_restriction instanceof CompositeExpressionInterface && $previous_restriction->getType() === CompositionType::Conjunction) {
                $restriction = $previous_restriction->with((string) $restriction);
            } else {
                $restriction = CompositeExpression::and($previous_restriction, $restriction);
            }
        }

        return $this->having($restriction);
    }

    /**
     * @inheritDoc
     */
    public function having(CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $restriction,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function orHaving(CompositeExpressionInterface|string $restriction): SelectQueryInterface
    {
        $previous_restriction = $this->having;
        if (null !== $previous_restriction) {
            if ($previous_restriction instanceof CompositeExpressionInterface && $previous_restriction->getType() === CompositionType::Disjunction) {
                $restriction = $previous_restriction->with((string) $restriction);
            } else {
                $restriction = CompositeExpression::or($previous_restriction, $restriction);
            }
        }

        return $this->having($restriction);
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static
    {
        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            [$sort => $direction],
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function andOrderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static
    {
        $order_by = $this->orderBy;
        $order_by[$sort] = $direction;

        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $order_by,
            $this->offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function offset(int $offset): static
    {
        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $offset,
            $this->limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function limit(int $limit): static
    {
        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $limit,
            $this->where,
        );
    }

    /**
     * @inheritDoc
     */
    public function where(CompositeExpressionInterface|string $expression): static
    {
        return new static(
            $this->dbal,
            $this->select,
            $this->distinct,
            $this->from,
            $this->joins,
            $this->groupBy,
            $this->having,
            $this->orderBy,
            $this->offset,
            $this->limit,
            $expression,
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . Str\join($this->select, ', ')
            . $this->getFromSQL()
            . $this->getWhereSQL()
            . $this->getGroupBySQL()
            . $this->getHavingSQL()
            . $this->getOrderBySQL()
            . $this->getLimitSQL();
    }

    /**
     * @throws LogicException If the query state is not valid.
     */
    private function getFromSQL(): string
    {
        return $this->from ? (' FROM ' . $this->getFromClausesSQL()) : '';
    }

    /**
     * @throws LogicException If the query state is not valid.
     */
    private function getFromClausesSQL(): string
    {
        $from_clauses = [];
        $known_aliases = [];

        // Loop through all FROM clauses
        foreach ($this->from as [$table, $alias]) {
            $reference = $alias ?? $table;

            $known_aliases[] = $reference;
            [$joins, $known_aliases] = $this->getJoinSQL($reference, $known_aliases);
            $from_clauses[$reference] = $table . ($alias === null ? '' : ' ' . $alias) . $joins;
        }

        $this->verifyAllAliasesAreKnown($known_aliases);

        return Str\join(Vec\values($from_clauses), ', ');
    }

    /**
     * @param non-empty-string $table
     * @param list<non-empty-string> $known_aliases
     *
     * @throws LogicException If the query state is not valid.
     *
     * @return array{string, list<non-empty-string>}
     */
    private function getJoinSQL(string $table, array $known_aliases): array
    {
        $sql = '';
        if (Iter\contains_key($this->joins, $table)) {
            foreach ($this->joins[$table] as [$kind, $join, $alias, $condition]) {
                if (Iter\contains($known_aliases, $alias)) {
                    throw new LogicException(Str\format(
                        'The given alias `%s` is not unique in FROM or JOIN clause table. The currently registered aliases are: `%s`.',
                        $alias,
                        Str\join($known_aliases, '`, `'),
                    ));
                }

                $sql .= ' ' . $kind->value . ' JOIN ' . $join . ' ' . $alias;
                if ($condition !== null) {
                    $sql .= ' ON ' . $condition;
                }

                $known_aliases[] = $alias;
            }

            foreach ($this->joins[$table] as [$_kind, $_join, $alias, $_condition]) {
                [$join_sql, $known_aliases] = $this->getJoinSQL($alias, $known_aliases);

                $sql .= $join_sql;
            }
        }

        return [$sql, $known_aliases];
    }

    /**
     * @param list<non-empty-string> $known_aliases
     *
     * @throws LogicException If the query state is not valid.
     */
    private function verifyAllAliasesAreKnown(array $known_aliases): void
    {
        foreach ($this->joins as $alias => $_) {
            if (!Iter\contains($known_aliases, $alias)) {
                throw new LogicException(Str\format(
                    'The given alias `%s` is not part of any FROM or JOIN clause table. The currently registered aliases are: `%s`.',
                    $alias,
                    Str\join($known_aliases, '`, `'),
                ));
            }
        }
    }

    private function getGroupBySQL(): string
    {
        if ([] === $this->groupBy) {
            return '';
        }

        return ' GROUP BY ' . Str\join($this->groupBy, ', ');
    }

    private function getHavingSQL(): string
    {
        if ($this->having === null) {
            return '';
        }

        return ' HAVING ' . ((string)$this->having);
    }

    private function getOrderBySQL(): string
    {
        if ($this->orderBy === []) {
            return '';
        }

        return ' ORDER BY ' . Str\join(
            Vec\map_with_key(
                $this->orderBy,
                static fn(string $sort, OrderDirection $direction): string => $sort . ' ' . $direction->value,
            ),
            ', ',
        );
    }

    private function getLimitSQL(): string
    {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= Str\format(' LIMIT %d', $this->limit);

            if ($this->offset > 0) {
                $sql .= Str\format(' OFFSET %d', $this->offset);
            }
        }

        return $sql;
    }

    /**
     * @inheritDoc
     */
    public function fetchOneNumeric(array $parameters = []): ?array
    {
        $row = $this->fetchOneAssociative();
        if (null === $row) {
            return null;
        }

        return Vec\values($row);
    }

    /**
     * @inheritDoc
     */
    public function fetchOneAssociative(array $parameters = []): ?array
    {
        $result = $this->execute($parameters);
        $rows = $result->getRows();
        unset($result);

        return $rows[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function fetchAllNumeric(array $parameters = []): array
    {
        return Vec\map($this->fetchAllAssociative($parameters), Vec\values(...));
    }

    /**
     * @inheritDoc
     */
    public function fetchAllAssociative(array $parameters = []): array
    {
        return $this->execute($parameters)->getRows();
    }
}
