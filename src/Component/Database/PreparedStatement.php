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

namespace Neu\Component\Database;

use Amp\Sql\SqlStatement;

final readonly class PreparedStatement implements PreparedStatementInterface
{
    /**
     * @param non-empty-string $sql The SQL query template used to prepare this statement.
     */
    public function __construct(
        private string $sql,
        private SqlStatement $statement,
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function execute(array $parameters = []): QueryResultInterface
    {
        return new QueryResult($this->statement->execute($parameters));
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getSqlTemplate(): string
    {
        return $this->sql;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getUnderlyingStatement(): SqlStatement
    {
        return $this->statement;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLastUsedAt(): int
    {
        return $this->statement->getLastUsedAt();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->statement->isClosed();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        $this->statement->close();
    }
}
