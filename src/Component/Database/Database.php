<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Postgres\PostgresConnection;
use Amp\Sql\SqlConnection;
use Amp\Sql\SqlConnectionException;
use Amp\Sql\SqlException;
use Amp\Sql\SqlQueryError;
use Amp\Sql\SqlTransactionIsolationLevel;
use Closure;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\Exception\UnsupportedFeatureException;
use Neu\Component\Database\Notification\ListenerInterface;
use Neu\Component\Database\Notification\Postgres\PostgresListener;
use Throwable;

final readonly class Database extends Link implements DatabaseInterface
{
    use AbstractionLayerConvenienceMethodsTrait;

    public function __construct(
        private SqlConnection $connection,
    ) {
        parent::__construct($this->connection);
    }

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function transactional(Closure $operation, TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): mixed
    {
        $transaction = $this->createTransaction($isolation);
        try {
            $result = $operation($transaction);
            /** @psalm-suppress MissingThrowsDocblock */
            $transaction->commit();

            return $result;
        } catch (Throwable $exception) {
            /** @psalm-suppress MissingThrowsDocblock */
            $transaction->rollback();

            /** @psalm-suppress MissingThrowsDocblock */
            throw $exception;
        }
    }

    /**
     * @inheritDoc
     */
    public function createTransaction(TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): TransactionInterface
    {
        $this->connection->setTransactionIsolation(match ($isolation) {
            TransactionIsolationLevel::ReadCommitted => SqlTransactionIsolationLevel::Committed,
            TransactionIsolationLevel::ReadUncommitted => SqlTransactionIsolationLevel::Uncommitted,
            TransactionIsolationLevel::RepeatableRead => SqlTransactionIsolationLevel::Repeatable,
            TransactionIsolationLevel::Serializable => SqlTransactionIsolationLevel::Serializable,
        });

        $transaction = $this->connection->beginTransaction();

        return new Transaction($transaction);
    }

    /**
     * @inheritDoc
     */
    public function getUnderlyingSqlConnection(): SqlConnection
    {
        return $this->connection;
    }
}
