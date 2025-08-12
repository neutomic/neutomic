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

use Amp\Postgres\PostgresConnection;
use Amp\Sql\SqlConfig;
use Amp\Sql\SqlConnection;
use Amp\Sql\SqlConnectionException;
use Amp\Sql\SqlException;
use Amp\Sql\SqlQueryError;
use Amp\Sql\SqlResult;
use Amp\Sql\SqlStatement;
use Amp\Sql\SqlTransaction;
use Amp\Sql\SqlTransactionIsolationLevel;
use Closure;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\Exception\TransactionException;
use Neu\Component\Database\Exception\UnsupportedFeatureException;
use Neu\Component\Database\Notification\ListenerInterface;
use Neu\Component\Database\Notification\Postgres\PostgresListener;
use Throwable;

final readonly class Database extends Link implements DatabaseInterface
{
    use AbstractionLayerConvenienceMethodsTrait;

    /**
     * The database platform.
     */
    private Platform $platform;

    /**
     * The SQL connection.
     *
     * @var SqlConnection<SqlConfig, SqlResult, SqlStatement<SqlResult>, SqlTransaction>
     */
    private SqlConnection $connection;

    /**
     * @param SqlConnection<SqlConfig, SqlResult, SqlStatement<SqlResult>, SqlTransaction> $connection
     */
    public function __construct(SqlConnection $connection)
    {
        if ($connection instanceof PostgresConnection) {
            $platform = Platform::Postgres;
        } else {
            $platform = Platform::Mysql;
        }

        parent::__construct($platform, $connection);

        $this->platform = $platform;
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getListener(string $channel): ListenerInterface
    {
        if ($this->connection instanceof PostgresConnection) {
            try {
                $postgresListener = $this->connection->listen($channel);

                return new PostgresListener($postgresListener, $channel);
            } catch (SqlConnectionException $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            } catch (SqlException | SqlQueryError $e) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new UnsupportedFeatureException('The database connection does not support notifications.');
    }

    /**
     * Run the given operation in a transaction, with the given isolation level.
     *
     * Note: any exception throw from the `$operation` will be thrown back to the caller site.
     *
     * @template T
     *
     * @param (Closure(TransactionInterface): T) $operation
     *
     * @throws TransactionException If failed to commit or rollback the transaction.
     *
     * @return T
     */
    #[\Override]
    public function transactional(Closure $operation, TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): mixed
    {
        $transaction = $this->createTransaction($isolation);
        try {
            $result = $operation($transaction);
            $transaction->commit();

            /** @var T */
            return $result;
        } catch (Throwable $exception) {
            $transaction->rollback();

            /** @psalm-suppress MissingThrowsDocblock */
            throw $exception;
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function createTransaction(TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): TransactionInterface
    {
        $this->connection->setTransactionIsolation(match ($isolation) {
            TransactionIsolationLevel::ReadCommitted => SqlTransactionIsolationLevel::Committed,
            TransactionIsolationLevel::ReadUncommitted => SqlTransactionIsolationLevel::Uncommitted,
            TransactionIsolationLevel::RepeatableRead => SqlTransactionIsolationLevel::Repeatable,
            TransactionIsolationLevel::Serializable => SqlTransactionIsolationLevel::Serializable,
        });

        $transaction = $this->connection->beginTransaction();

        return new Transaction($this->platform, $transaction);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getUnderlyingSqlConnection(): SqlConnection
    {
        return $this->connection;
    }
}
