<?php

declare(strict_types=1);

namespace Neu\Component\Database\DependencyInjection\Factory;

use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\DatabaseManager;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see DatabaseManager} instance.
 *
 * @implements FactoryInterface<DatabaseManager>
 */
final readonly class DatabaseManagerFactory implements FactoryInterface
{
    /**
     * The identifier for the default database.
     */
    private string $defaultDatabaseId;

    /**
     * An array of container services identifiers, indexed by the locator service identifier.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $services;

    /**
     * Create a new {@see DatabaseManagerFactory} instance.
     *
     * @param string $defaultDatabaseId The identifier for the default database.
     * @param array<non-empty-string, non-empty-string> $services An array of container services identifiers, indexed by the locator service identifier.
     */
    public function __construct(string $defaultDatabaseId, array $services)
    {
        $this->services = $services;
        $this->defaultDatabaseId = $defaultDatabaseId;
    }

    /**
     * Create a new {@see DatabaseManager} instance.
     *
     * @param ContainerInterface $container The container used to retrieve the services.
     *
     * @return DatabaseManager
     */
    public function __invoke(ContainerInterface $container): object
    {
        $locator = $container->getLocator(DatabaseInterface::class, $this->services);
        if (!$locator->has($this->defaultDatabaseId)) {
            throw new RuntimeException('The default database is not defined.');
        }

        return new DatabaseManager($this->defaultDatabaseId, $locator);
    }
}
