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

interface DatabaseManagerInterface
{
    /**
     * Retrieve the default database.
     *
     * @throws Exception\RuntimeException If failed to load the default database.
     *
     * @return DatabaseInterface The default database.
     */
    public function getDefaultDatabase(): DatabaseInterface;

    /**
     * Check if a database with the given name is registered.
     *
     * @param non-empty-string $name The unique name for the database.
     *
     * @return bool True if the database is registered, false otherwise.
     */
    public function hasDatabase(string $name): bool;

    /**
     * Retrieve a database by its name.
     *
     * @param non-empty-string $name The unique name for the database.
     *
     * @throws Exception\DatabaseNotFoundException If the database with the given name is not found.
     * @throws Exception\RuntimeException If failed to load the database.
     *
     * @return DatabaseInterface The database associated with the given name.
     */
    public function getDatabase(string $name): DatabaseInterface;

    /**
     * Retrieve the list of available databases.
     *
     * @throws Exception\RuntimeException If failed to load the list of available databases.
     *
     * @return list<DatabaseInterface> The list of available databases.
     */
    public function getAvailableDatabases(): array;
}
