<?php

declare(strict_types=1);

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
}
