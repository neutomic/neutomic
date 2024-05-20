<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Mysql\MysqlLink;
use Amp\Postgres\PostgresExecutor;
use Amp\Postgres\PostgresLink;
use Amp\Sql\SqlConnectionException;
use Amp\Sql\SqlException;
use Amp\Sql\SqlLink;
use Amp\Sql\SqlQueryError;
use Closure;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\InvalidQueryException;
use Neu\Component\Database\Exception\RuntimeException;
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
    private SqlLink $link;
    private Platform $platform;

    public function __construct(SqlLink $link)
    {
        $this->link = $link;
        if ($this->link instanceof PostgresLink) {
            $this->platform = Platform::Postgres;
        } elseif ($this->link instanceof MysqlLink) {
            $this->platform = Platform::Mysql;
        } else {
            throw new UnsupportedFeatureException('Unsupported database platform.');
        }
    }

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function transactional(Closure $operation): mixed
    {
        $transaction = $this->createTransaction();
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
    public function createTransaction(): TransactionInterface
    {
        return new Transaction(
            $this->link->beginTransaction()
        );
    }

    /**
     * @inheritDoc
     */
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
    public function getUnderlyingSqlLink(): SqlLink
    {
        return $this->link;
    }

    /**
     * @inheritDoc
     */
    public function getUnderlyingPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @inheritDoc
     */
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
    public function getLastUsedAt(): int
    {
        return $this->link->getLastUsedAt();
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->link->isClosed();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->link->close();
    }
}
