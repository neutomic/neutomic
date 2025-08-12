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
    #[\Override]
    public function nextQueryResult(): null|QueryResultInterface
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
    #[\Override]
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
    #[\Override]
    public function getRowCount(): null|int
    {
        if ($this->result instanceof SqlCommandResult) {
            return 0;
        }

        return $this->getAffectedRowCount();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAffectedRowCount(): null|int
    {
        /** @var null|int<0, max> */
        return $this->result->getRowCount();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getUnderlyingSqlResult(): SqlResult
    {
        return $this->result;
    }
}
