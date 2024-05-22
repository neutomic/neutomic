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
use Amp\Sql\SqlTransactionError;
use Neu\Component\Database\Exception\TransactionException;

final readonly class Transaction extends Link implements TransactionInterface
{
    use AbstractionLayerConvenienceMethodsTrait;

    private SqlTransaction $transaction;

    public function __construct(Platform $platform, SqlTransaction $transaction)
    {
        parent::__construct($platform, $transaction);

        $this->transaction = $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getIsolationLevel(): TransactionIsolationLevel
    {
        return match ($this->transaction->getIsolation()->getLabel()) {
            'Uncommitted' => TransactionIsolationLevel::ReadUncommitted,
            'Committed' => TransactionIsolationLevel::ReadCommitted,
            'Repeatable' => TransactionIsolationLevel::RepeatableRead,
            'Serializable' => TransactionIsolationLevel::Serializable,
            default => throw new TransactionException('Unknown isolation level.'),
        };
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->transaction->isActive();
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        try {
            $this->transaction->commit();
        } catch (SqlTransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        try {
            $this->transaction->rollback();
        } catch (SqlTransactionError $e) {
            throw new TransactionException($e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getUnderlyingSqlTransaction(): SqlTransaction
    {
        return $this->transaction;
    }
}
