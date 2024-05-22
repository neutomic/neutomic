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

use Neu\Component\Database\Exception\DatabaseNotFoundException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Neu\Component\DependencyInjection\ServiceLocatorInterface;

final class DatabaseManager implements DatabaseManagerInterface
{
    /**
     * The name for the default database.
     *
     * @var non-empty-string
     */
    private string $defaultDatabaseId;

    /**
     * The service locator used to create database instances.
     *
     * @var ServiceLocatorInterface<DatabaseInterface>
     */
    private ServiceLocatorInterface $locator;

    /**
     * Create a new {@see DatabaseManager} instance.
     *
     * @param non-empty-string $defaultDatabaseId The id for the default database.
     * @param ServiceLocatorInterface<DatabaseInterface> $locator The service locator used to create database instances.
     */
    public function __construct(string $defaultDatabaseId, ServiceLocatorInterface $locator)
    {
        $this->defaultDatabaseId = $defaultDatabaseId;
        $this->locator = $locator;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDatabase(): DatabaseInterface
    {
        return $this->getDatabase($this->defaultDatabaseId);
    }

    /**
     * @inheritDoc
     */
    public function hasDatabase(string $name): bool
    {
        return $this->locator->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getDatabase(string $name): DatabaseInterface
    {
        try {
            return $this->locator->get($name);
        } catch (ServiceNotFoundException $exception) {
            throw DatabaseNotFoundException::forDatabase($name, $exception);
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException('An error occurred while retrieving the database.', previous: $exception);
        }
    }
}
