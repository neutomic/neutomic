<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query\Builder\Internal;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\IdentifierQuoterInterface;
use Neu\Component\Database\PreparedStatementInterface;
use Neu\Component\Database\Query\QueryInterface;
use Neu\Component\Database\QueryResultInterface;

/**
 * A Query that can be executed.
 *
 * @internal
 */
abstract readonly class AbstractExecutableQuery implements QueryInterface
{
    public function __construct(
        protected AbstractionLayerInterface $dbal,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(array $parameters = []): QueryResultInterface
    {
        return $this->dbal->query((string) $this, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): PreparedStatementInterface
    {
        return $this->dbal->prepare((string) $this);
    }

    /**
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     *
     * @throws ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string
     */
    protected function getTableSQL(string $table, ?string $alias = null): string
    {
        if ($this->dbal instanceof IdentifierQuoterInterface) {
            return $this->dbal->quoteIdentifier($table) . ($alias !== null ? (' ' . $this->dbal->quoteIdentifier($alias)) : '');
        }

        return $table . ($alias !== null ? (' ' . $alias) : '');
    }
}
