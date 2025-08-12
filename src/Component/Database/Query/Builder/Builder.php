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

namespace Neu\Component\Database\Query\Builder;

use Neu\Component\Database\AbstractionLayerInterface;
use Neu\Component\Database\Query\Builder\Internal\DeleteQuery;
use Neu\Component\Database\Query\Builder\Internal\InsertQuery;
use Neu\Component\Database\Query\Builder\Internal\SelectQuery;
use Neu\Component\Database\Query\Builder\Internal\UpdateQuery;
use Neu\Component\Database\Query\DeleteQueryInterface;
use Neu\Component\Database\Query\InsertQueryInterface;
use Neu\Component\Database\Query\SelectQueryInterface;
use Neu\Component\Database\Query\UpdateQueryInterface;

final readonly class Builder implements BuilderInterface
{
    public function __construct(
        private AbstractionLayerInterface $dbal
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function select(string $select, string ...$selects): SelectQueryInterface
    {
        /** @psalm-suppress ArgumentTypeCoercion - false positive */
        return new SelectQuery($this->dbal, [$select, ...$selects]);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function delete(string $table, null|string $alias = null): DeleteQueryInterface
    {
        return new DeleteQuery($this->dbal, $table, $alias);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function update(string $table, null|string $alias = null): UpdateQueryInterface
    {
        return new UpdateQuery($this->dbal, $table, $alias);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function insert(string $table): InsertQueryInterface
    {
        return new InsertQuery($this->dbal, $table);
    }
}
