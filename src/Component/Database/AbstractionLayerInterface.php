<?php

declare(strict_types=1);

namespace Neu\Component\Database;

use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\InvalidArgumentException;
use Neu\Component\Database\Exception\InvalidQueryException;
use Neu\Component\Database\Exception\LogicException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\Query\Builder\BuilderInterface;

interface AbstractionLayerInterface extends LinkInterface
{
    /**
     * Creates a query builder that can be used to execute queries through the abstraction layer.
     */
    public function createQueryBuilder(): BuilderInterface;

    /**
     * Creates an expression builder to be used for building queries.
     */
    public function createExpressionBuilder(): Query\Expression\BuilderInterface;

    /**
     * Insert one row into the given table.
     *
     * Example:
     *
     * ```php
     * $database->insert('users', ['username' => 'azjezz', 'password' => $hash]);
     * ```
     *
     * @param non-empty-string $table
     * @param array<non-empty-string, mixed> $row
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     */
    public function insert(string $table, array $row): QueryResultInterface;

    /**
     * Insert multiple rows into the given table.
     *
     * ```php
     * $database->insert('notes', [
     *  ['content' => 'Hello, World!'],
     *  ['content' => 'from Neu!'],
     * ]);
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<array<non-empty-string, mixed>> $rows
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws LogicException If $rows is empty, or have inconsistent column names.
     */
    public function insertAll(string $table, array $rows): QueryResultInterface;

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * Example:
     *
     * ```php
     * $database->update('users', ['password' => $hash], criteria: ['username' => 'azjezz']);
     * ```
     *
     * @param non-empty-string $table Table name
     * @param non-empty-array<non-empty-string, mixed> $data Column-value pairs
     * @param non-empty-array<non-empty-string, mixed> $criteria Update criteria
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws LogicException If $criteria, or $data are empty.
     */
    public function update(string $table, array $data, array $criteria): QueryResultInterface;

    /**
     * Executes an SQL DELETE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * Example:
     *
     * ```php
     * $database->delete('users', criteria: ['username' => 'azjezz']);
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-array<non-empty-string, mixed> $criteria Delete criteria
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error)
     * @throws LogicException If $criteria is empty.
     */
    public function delete(string $table, array $criteria): QueryResultInterface;

    /**
     * Fetch one row from the given table, where columns are index using their names.
     *
     * Example:
     *
     * ```php
     *  $row = $database->fetchOne('articles', ['title', 'content'], criteria: ['id' => 341]);
     *  if (null === $row) {
     *    // handle not found
     *  }
     *
     *  ['title' => $title, 'content' => $content] = $row;
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<string, mixed> $criteria
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     * @throws RuntimeException If the operation fails due to unexpected condition.
     *
     * @return null|array<string, mixed>
     */
    public function fetchOneAssociative(string $table, array $fields = ['*'], array $criteria = [], array $order_by = []): ?array;

    /**
     * Fetch one row from the given table, where columns are index using numeric values.
     *
     * Example:
     *
     * ```php
     *  $row = $database->fetchOne('articles', ['title', 'content'], criteria: ['id' => 341]);
     *  if (null === $row) {
     *    // handle not found
     *  }
     *
     *  [$title, $content] = $row;
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<string, mixed> $criteria
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     * @throws RuntimeException If the operation fails due to unexpected condition.
     *
     * @return null|list<mixed>
     */
    public function fetchOneNumeric(string $table, array $fields = ['*'], array $criteria = [], array $order_by = []): ?array;

    /**
     * Fetch one, or more row from the given table, where columns are index using their names.
     *
     * Example:
     *
     * ```php
     *  $articles = $database->fetchAll('articles', ['title', 'content'], criteria: ['author' => 'azjezz'], limit: 10);
     *  foreach($articles as $article) {
     *      ['title' => $title, 'content' => $content] = $article;
     *      // do something...
     *  }
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<non-empty-string, mixed> $criteria
     * @param int<0, max>|null $offset
     * @param int<0, max>|null $limit
     * @param array<non-empty-string, OrderDirection> $order_by
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAllAssociative(string $table, array $fields = ['*'], array $criteria = [], ?int $offset = null, ?int $limit = null, array $order_by = []): array;

    /**
     * Fetch one, or more row from the given table, where columns are index using numeric values.
     *
     * Example:
     *
     * ```php
     *  $articles = $database->fetchAllNumeric('articles', ['title', 'content'], criteria: ['author' => 'azjezz'], offset: 2, limit: 10);
     *  foreach($articles as $article) {
     *      [$title, $content] = $article;
     *      // do something...
     *  }
     * ```
     *
     * @param non-empty-string $table
     * @param non-empty-list<non-empty-string> $fields
     * @param array<non-empty-string, mixed> $criteria
     * @param int<0, max>|null $offset
     * @param int<0, max>|null $limit
     * @param array<non-empty-string, OrderDirection> $orderBy
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     * @throws InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     *
     * @return list<list<mixed>>
     */
    public function fetchAllNumeric(string $table, array $fields = ['*'], array $criteria = [], ?int $offset = null, ?int $limit = null, array $orderBy = []): array;
}
