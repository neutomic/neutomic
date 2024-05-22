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

use Amp\Sql\SqlResult;

interface QueryResultInterface
{
    /**
     * Return the next query result if available.
     */
    public function nextQueryResult(): null|QueryResultInterface;

    /**
     * Returns the actual rows returned by the successful query, each row including the typed values for each column.
     *
     * All values come back as the actual typed representation of the database type.
     *
     * @return list<array<string, mixed>>
     */
    public function getRows(): array;

    /**
     * The number of rows in the current result, or null if the number of rows is
     * unknown or not applicable to the query.
     *
     * This is particularly useful for `SELECT` statements.
     *
     * This is complementary to {@see getAffectedRowCount()} as they might be the same value, but if this was an `INSERT` query,
     * for example, then this might be 0, while {@see getAffectedRowCount()} could be non-zero.
     *
     * Example:
     *
     * ```php
     * $result = $connection->query('SELECT username FROM users');
     *
     * assert($result->getRawsCount() === count($result->getRaws()));
     * ```
     *
     * @return null|int<0, max>
     */
    public function getRowCount(): null|int;

    /**
     * The number of database rows affected in the current result, or null if the number of rows is
     * unknown or not applicable to the query.
     *
     * This is particularly useful for `INSERT`, `DELETE`, and `UPDATE` statements.
     *
     * This is complementary to {@see getRowCount()} as they might be the same value, but if this was an `INSERT` query,
     * for example, then this might be non-zero value, while {@see getRowCount()} would be 0.
     *
     * Example:
     *
     * ```php
     * $result = $connection->query('INSERT INTO users (username, email, age) VALUES ("azjezz", "azjezz@protonmail.com", 22)');
     * assert($result->getAffectedRowsCount() === 1);
     * ```
     *
     * @return null|int<0, max>
     */
    public function getAffectedRowCount(): null|int;

    /**
     * Get the underlying SQL result.
     */
    public function getUnderlyingSqlResult(): SqlResult;
}
