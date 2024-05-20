<?php

declare(strict_types=1);

namespace Neu\Component\Database\DependencyInjection\Factory;

use Amp\Sql\SqlConnection;
use Neu\Component\Database\Database;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * Factory for creating a {@see Database} instance.
 *
 * @implements FactoryInterface<Database>
 */
final readonly class DatabaseFactory implements FactoryInterface
{
    private string $connection;

    /**
     * @param string|null $connection The connection service identifier, defaults to {@see SqlConnection::class}.
     */
    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?? SqlConnection::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Database
    {
        return new Database(
            $container->getTyped($this->connection, SqlConnection::class),
        );
    }
}
