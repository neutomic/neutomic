<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Amp\Sql\Common\SqlCommandResult;
use Amp\Sql\SqlResult;

final readonly class QueryResult implements QueryResultInterface
{
    public function __construct(
        private SqlResult $result,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function nextQueryResult(): ?QueryResultInterface
    {
        $next = $this->result->getNextResult();
        if ($next === null) {
            return null;
        }

        return new self($next);
    }

    /**
     * @inheritDoc
     */
    public function getRows(): array
    {
        $rows = [];
        /** @var array<string, mixed> $row */
        foreach ($this->result as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function getRowCount(): ?int
    {
        if ($this->result instanceof SqlCommandResult) {
            return 0;
        }

        return $this->getAffectedRowCount();
    }

    /**
     * @inheritDoc
     */
    public function getAffectedRowCount(): ?int
    {
        /** @var null|int<0, max> */
        return $this->result->getRowCount();
    }

    /**
     * @inheritDoc
     */
    public function getUnderlyingSqlResult(): SqlResult
    {
        return $this->result;
    }
}
