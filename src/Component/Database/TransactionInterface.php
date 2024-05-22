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

use Amp\Sql\SqlTransaction;
use Neu\Component\Database\Exception\TransactionException;

interface TransactionInterface extends AbstractionLayerInterface
{
    /**
     * Get the transaction isolation level.
     */
    public function getIsolationLevel(): TransactionIsolationLevel;

    /**
     * @return bool True if the transaction is active, false if it has been committed or rolled back.
     */
    public function isActive(): bool;

    /**
     * Commits the transaction and makes it inactive.
     *
     * @throws TransactionException If the transaction has been committed or rolled back.
     */
    public function commit(): void;

    /**
     * Rolls back the transaction and makes it inactive.
     *
     * @throws TransactionException If the transaction has been committed or rolled back.
     */
    public function rollback(): void;

    /**
     * Get the underlying SQL transaction.
     */
    public function getUnderlyingSqlTransaction(): SqlTransaction;
}
