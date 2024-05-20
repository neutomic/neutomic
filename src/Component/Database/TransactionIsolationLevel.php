<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Sql\SqlTransactionIsolationLevel;

enum TransactionIsolationLevel
{
    /**
     * This is the highest isolation level.
     *
     * With a lock-based concurrency control DBMS implementation, serializability requires read and write locks (acquired on selected data) to be released
     * at the end of the transaction.
     *
     * Range-locks must be acquired when a `SELECT` query uses a ranged `WHERE` clause, especially to avoid the phantom read phenomenon.
     */
    case Serializable;

    /**
     * In this isolation level, a lock-based concurrency control DBMS implementation keeps read and write locks (acquired on selected data) until the end of the transaction.
     * However, range-locks are not managed, so phantom reads can occur.
     */
    case RepeatableRead;

    /**
     * In this isolation level, a lock-based concurrency control DBMS implementation keeps write locks (acquired on selected data) until the end
     * of the transaction, but read locks are released as soon as the SELECT operation is performed (so the non-repeatable reads phenomenon can occur in this isolation level).
     *
     * As in the previous level, range-locks are not managed.
     */
    case ReadCommitted;

    /**
     * This is the lowest isolation level. In this level, dirty reads are allowed, so one transaction may see not-yet-committed changes made by other transactions.
     *
     * @note PostgreSQL's ReadUncommitted level behaves like ReadCommitted. This is because it is the only sensible way to map the standard isolation levels to PostgreSQL's MVCC architecture.
     */
    case ReadUncommitted;

    /**
     * Convert the given {@see SqlTransactionIsolationLevel} into a {@see TransactionIsolationLevel}.
     */
    public static function fromSqlTransactionIsolationLevel(SqlTransactionIsolationLevel $level): self
    {
        return match ($level) {
            SqlTransactionIsolationLevel::Serializable => self::Serializable,
            SqlTransactionIsolationLevel::Repeatable => self::RepeatableRead,
            SqlTransactionIsolationLevel::Committed => self::ReadCommitted,
            SqlTransactionIsolationLevel::Uncommitted => self::ReadUncommitted,
        };
    }

    /**
     * Convert this {@see TransactionIsolationLevel} into a {@see SqlTransactionIsolationLevel}.
     */
    public function toSqlTransactionIsolationLevel(): SqlTransactionIsolationLevel
    {
        return match ($this) {
            self::Serializable => SqlTransactionIsolationLevel::Serializable,
            self::RepeatableRead => SqlTransactionIsolationLevel::Repeatable,
            self::ReadCommitted => SqlTransactionIsolationLevel::Committed,
            self::ReadUncommitted => SqlTransactionIsolationLevel::Uncommitted,
        };
    }
}
