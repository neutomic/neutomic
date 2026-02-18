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
