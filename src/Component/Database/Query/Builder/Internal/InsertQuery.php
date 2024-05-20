<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Exception\LogicException;
use Neu\Component\Database\Query\InsertQueryInterface;
use Neu\Component\Database\Query\Type;
use Psl\Str;
use Psl\Vec;

use function array_merge;

final readonly class InsertQuery extends AbstractExecutableQuery implements InsertQueryInterface
{
    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     * @param list<array<non-empty-string, string>> $values
     */
    public function __construct(
        AbstractionLayerInterface $dbal,
        private string $table,
        private null|string $alias = null,
        private array $values = [],
    ) {
        parent::__construct($dbal);
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If no values have been provided, or an inconsistent value is encountered.
     */
    public function __toString(): string
    {
        $columns = null;
        $sets = [];
        foreach ($this->values as $i => $row) {
            $row_columns = Vec\keys($row);
            if ($columns === null) {
                $columns = $row_columns;
            } elseif ($columns !== $row_columns) {
                throw new LogicException(Str\format('All values must have consistent column names, value #%d is inconsistent.', $i));
            }

            $sets[] = '(' . Str\join(Vec\values($row), ', ') . ')';
        }

        if ($columns === null) {
            throw new LogicException('InsertQueryInterface::values() must be called at least once before attempting to execute the insert query.');
        }

        return 'INSERT INTO ' . $this->getTableSQL($this->table, $this->alias) . ' (' . Str\join($columns, ', ') . ') VALUES ' . Str\join($sets, ', ');
    }

    /**
     * @inheritDoc
     */
    public function values(array $first, array ...$rest): static
    {
        $values = array_merge([$first], $rest);

        return new static($this->dbal, $this->table, $this->alias, $values);
    }

    /**
     * @inheritDoc
     */
    public function getType(): Type
    {
        return Type::Insert;
    }
}
