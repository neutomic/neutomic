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

namespace Neu\Component\Database\DependencyInjection\Factory;

use Amp\Sql\SqlConfig;
use Amp\Sql\SqlConnection;
use Amp\Sql\SqlResult;
use Amp\Sql\SqlStatement;
use Amp\Sql\SqlTransaction;
use Neu\Component\Database\Database;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Override;

/**
 * Factory for creating a {@see Database} instance.
 *
 * @implements FactoryInterface<Database>
 */
final readonly class DatabaseFactory implements FactoryInterface
{
    /**
     * The connection service identifier.
     *
     * @var non-empty-string
     */
    private string $connection;

    /**
     * @param non-empty-string|null $connection The connection service identifier, defaults to {@see SqlConnection::class}.
     */
    public function __construct(null|string $connection = null)
    {
        $this->connection = $connection ?? SqlConnection::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(ContainerInterface $container): Database
    {
        /** @var SqlConnection<SqlConfig, SqlResult, SqlStatement<SqlResult>, SqlTransaction> $connection */
        $connection = $container->getTyped($this->connection, SqlConnection::class);

        return new Database($connection);
    }
}
