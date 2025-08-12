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
    #[\Override]
    public function execute(array $parameters = []): QueryResultInterface
    {
        return $this->dbal->query((string) $this, $parameters);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
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
    protected function getTableSQL(string $table, null|string $alias = null): string
    {
        if ($this->dbal instanceof IdentifierQuoterInterface) {
            return $this->dbal->quoteIdentifier($table) . ($alias !== null ? (' ' . $this->dbal->quoteIdentifier($alias)) : '');
        }

        return $table . ($alias !== null ? (' ' . $alias) : '');
    }
}
