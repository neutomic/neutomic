<?php

declare(strict_types=1);

namespace Neu\Component\Database\Notification\Postgres;

use Amp\Postgres\PostgresExecutor;
use Amp\Sql\SqlConnectionException;
use Amp\Sql\SqlException;
use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\Notification\NotifierInterface;
use Neu\Component\Database\QueryResult;
use Neu\Component\Database\QueryResultInterface;

final readonly class PostgresNotifier implements NotifierInterface
{
    /**
     * @param non-empty-string $channel
     */
    public function __construct(
        private PostgresExecutor $executor,
        private string $channel,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @inheritDoc
     */
    public function notify(string $message = ''): QueryResultInterface
    {
        try {
            $result = $this->executor->notify($this->channel, $message);
        } catch (SqlConnectionException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
        } catch (SqlException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return new QueryResult($result);
    }
}
