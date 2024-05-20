<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Sql\SqlConnection;
use Closure;
use Neu\Component\Database\Notification\ListenerInterface;

interface DatabaseInterface extends AbstractionLayerInterface
{
    /**
     * Creates a transaction that can be used to execute queries in isolation, with the given isolation level.
     */
    public function createTransaction(TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): TransactionInterface;

    /**
     * Run the given operation in a transaction, with the given isolation level.
     *
     * Note: any exception throw from the `$operation` will be thrown back to the caller site.
     *
     * @template T
     *
     * @param Closure(TransactionInterface): T $operation
     *
     * @return T
     */
    public function transactional(Closure $operation, TransactionIsolationLevel $isolation = TransactionIsolationLevel::ReadUncommitted): mixed;

    /**
     * Retrieve notification listener for the given channel.
     *
     * @param non-empty-string $channel The channel identifier
     *
     * @throws Exception\UnsupportedFeatureException If the database does not support notifications.
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     */
    public function getListener(string $channel): ListenerInterface;

    /**
     * Retrieve the underlying SQL connection.
     */
    public function getUnderlyingSqlConnection(): SqlConnection;
}
