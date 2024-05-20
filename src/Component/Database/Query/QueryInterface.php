<?php

declare(strict_types=1);

namespace Neu\Component\Database\Query;

use Neu\Component\Database\Exception;
use Neu\Component\Database\PreparedStatementInterface;
use Neu\Component\Database\QueryResultInterface;
use Stringable;

interface QueryInterface extends Stringable
{
    /**
     * Retrieve the query type.
     */
    public function getType(): Type;

    /**
     * Execute this query using the optionally provided `$parameters`.
     *
     * @param array<non-empty-string, mixed> $parameters
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If the query state is not valid.
     */
    public function execute(array $parameters = []): QueryResultInterface;

    /**
     * Prepares this query.
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\LogicException If the query state is not valid.
     */
    public function prepare(): PreparedStatementInterface;

    /**
     * Convert this query to a string.
     *
     * @throws Exception\LogicException If the query state is not valid.
     * @throws Exception\ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string
     */
    public function __toString(): string;
}
