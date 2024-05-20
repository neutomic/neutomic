<?php

declare(strict_types=1);

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
    public function execute(array $parameters = []): QueryResultInterface
    {
        return new QueryResult($this->statement->execute($parameters));
    }

    /**
     * @inheritDoc
     */
    public function getSqlTemplate(): string
    {
        return $this->sql;
    }

    /**
     * @inheritDoc
     */
    public function getUnderlyingStatement(): SqlStatement
    {
        return $this->statement;
    }

    /**
     * @inheritDoc
     */
    public function getLastUsedAt(): int
    {
        return $this->statement->getLastUsedAt();
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->statement->isClosed();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->statement->close();
    }
}
