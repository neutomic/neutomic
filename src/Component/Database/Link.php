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

use Amp\Mysql\MysqlLink;
use Amp\Postgres\PostgresExecutor;
use Amp\Postgres\PostgresLink;
use Amp\Sql\SqlConnectionException;
use Amp\Sql\SqlException;
use Amp\Sql\SqlLink;
use Amp\Sql\SqlQueryError;
use Amp\Sql\SqlResult;
use Amp\Sql\SqlStatement;
use Amp\Sql\SqlTransaction;
use Closure;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\InvalidQueryException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\Exception\TransactionException;
use Neu\Component\Database\Exception\UnsupportedFeatureException;
use Neu\Component\Database\Notification\NotifierInterface;
use Neu\Component\Database\Notification\Postgres\PostgresNotifier;
use Throwable;

use function array_map;
use function explode;
use function implode;
use function str_contains;
use function str_replace;

abstract readonly class Link implements LinkInterface
{
    private Platform $platform;

    /**
     * @var SqlLink<SqlResult, SqlStatement<SqlResult>, SqlTransaction>
     */
    private SqlLink $link;

    /**
     * @param SqlLink<SqlResult, SqlStatement<SqlResult>, SqlTransaction> $link
     */
    public function __construct(Platform $platform, SqlLink $link)
    {
        $this->platform = $platform;
        $this->link = $link;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function prepare(string $query): PreparedStatementInterface
    {
        try {
            $statement = $this->link->prepare($query);

            return new PreparedStatement($query, $statement);
        } catch (SqlQueryError $e) {
            throw new InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlConnectionException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function query(string $query, array $parameters = []): QueryResultInterface
    {
        try {
            if ($parameters === []) {
                // Allow multiple commands in a single query when not using prepared statement.
                $result = new QueryResult($this->link->query($query));
            } else {
                $result = new QueryResult($this->link->execute($query, $parameters));
            }

            return $result;
        } catch (SqlQueryError $e) {
            throw new InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlConnectionException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
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
    public function transactional(Closure $operation): mixed
    {
        $transaction = $this->createTransaction();
        try {
            $result = $operation($transaction);
            /** @psalm-suppress MissingThrowsDocblock */
            $transaction->commit();

            /** @var T */
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
    #[\Override]
    public function createTransaction(): TransactionInterface
    {
        return new Transaction(
            $this->platform,
            $this->link->beginTransaction()
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getNotifier(string $channel): NotifierInterface
    {
        if ($this->link instanceof PostgresExecutor) {
            return new PostgresNotifier($this->link, $channel);
        }

        throw new UnsupportedFeatureException('The database connection does not support notifications.');
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getUnderlyingSqlLink(): SqlLink
    {
        return $this->link;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getUnderlyingPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quoteIdentifier(string $identifier): string
    {
        if (str_contains($identifier, '.')) {
            $parts = array_map($this->quoteSingleIdentifier(...), explode('.', $identifier));

            return implode('.', $parts);
        }

        return $this->quoteSingleIdentifier($identifier);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quoteSingleIdentifier(string $identifier): string
    {
        if ($this->link instanceof MysqlLink) {
            return '`' . str_replace('`', '``', $identifier) . '`';
        }

        if ($this->link instanceof PostgresLink) {
            try {
                $this->link->quoteIdentifier($identifier);
            } catch (Throwable) {
                // fall back to default quoting
            }
        }

        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLastUsedAt(): int
    {
        return $this->link->getLastUsedAt();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->link->isClosed();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function close(): void
    {
        $this->link->close();
    }
}
