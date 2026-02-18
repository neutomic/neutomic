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

use Neu\Component\Database\Exception\ConnectionException;

interface IdentifierQuoterInterface
{
    /**
     * Quotes (escapes) the given string for use as a name or identifier in a query.
     *
     * @param non-empty-string $identifier Unquoted identifier.
     *
     * @throws ConnectionException If the connection to the database has been closed.
     *
     * @return non-empty-string Quoted identifier.
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Quotes a single identifier (table, column, etc.) name.
     *
     * @param non-empty-string $identifier The identifier name to be quoted.
     *
     * @return non-empty-string The quoted identifier string.
     */
    public function quoteSingleIdentifier(string $identifier): string;
}
