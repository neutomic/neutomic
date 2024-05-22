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

use Amp\Sql\SqlLink;
use Closure;
use Neu\Component\Database\Exception\TransactionException;
use Neu\Component\Database\Notification\NotifierInterface;

interface LinkInterface extends IdentifierQuoterInterface, ResourceInterface
{
    /**
     * Prepares an SQL statement.
     *
     * @param non-empty-string $query The SQL statement to prepare.
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function prepare(string $query): PreparedStatementInterface;

    /**
     * Execute the given `$query` using optionally provided `$parameters`.
     *
     * @param non-empty-string $query
     * @param array<string, mixed> $parameters
     *
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function query(string $query, array $parameters = []): QueryResultInterface;

    /**
     * Creates a transaction that can be used to execute queries in isolation.
     */
    public function createTransaction(): TransactionInterface;

    /**
     * Run the given operation in a transaction.
     *
     * Note: any exception throw from the `$operation` will be thrown back to the caller site.
     *
     * @template T
     *
     * @param Closure(TransactionInterface): T $operation
     *
     * @throws TransactionException If failed to commit or rollback the transaction.
     *
     * @return T
     */
    public function transactional(Closure $operation): mixed;

    /**
     * Retrieve a notifier for the given channel.
     *
     * @param non-empty-string $channel The channel identifier
     *
     * @throws Exception\UnsupportedFeatureException If the underlying database does not support notifications.
     */
    public function getNotifier(string $channel): NotifierInterface;

    /**
     * Get the underlying SQL link.
     */
    public function getUnderlyingSqlLink(): SqlLink;

    /**
     * Get the platform of the underlying link.
     */
    public function getUnderlyingPlatform(): Platform;
}
