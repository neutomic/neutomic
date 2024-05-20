<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Sql\SqlStatement;

interface PreparedStatementInterface extends ResourceInterface
{
    /**
     * Execute the prepared statement.
     *
     * @param array<string, mixed> $parameters
     */
    public function execute(array $parameters = []): QueryResultInterface;

    /**
     * Retrieve the SQL query template used to prepare this statement.
     */
    public function getSqlTemplate(): string;

    /**
     * Get the underlying SQL statement.
     */
    public function getUnderlyingStatement(): SqlStatement;
}
