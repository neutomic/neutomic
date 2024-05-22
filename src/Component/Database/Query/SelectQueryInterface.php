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

namespace Neu\Component\Database\Query;

use Neu\Component\Database\Exception;
use Neu\Component\Database\OrderDirection;

interface SelectQueryInterface extends QueryInterface, WhereQueryInterface
{
    /**
     * Adds DISTINCT to the query.
     *
     * Example:
     *
     *      $query = $database
     *          ->createQueryBuilder()
     *          ->select('u.id')
     *          ->from('users', 'u')
     *          ->distinct();
     */
    public function distinct(): static;

    /**
     * Creates and adds a query root corresponding to the table identified by the
     * given alias, forming a cartesian product with any existing query roots.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.id', 'u.username')
     *          ->from('users', 'u')
     *          ->execute();
     *
     * @param non-empty-string $table
     * @param null|non-empty-string $alias
     */
    public function from(string $table, null|string $alias = null): static;

    /**
     * Creates and adds a join to the query.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.id', 'u.username')
     *          ->from('users', 'u')
     *          ->innerJoin('u', 'phone_numbers', 'p', 'p.is_primary = 1')
     *          ->execute();
     *
     * @param non-empty-string $from The alias that points to a from clause.
     * @param non-empty-string $join The table name to join.
     * @param non-empty-string $alias The alias of the join table.
     * @param ?non-empty-string $condition The condition for the join.
     */
    public function innerJoin(string $from, string $join, string $alias, null|string $condition = null): static;

    /**
     * Creates and adds a left join to the query.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.id', 'u.username')
     *          ->from('users', 'u')
     *          ->leftJoin('u', 'phone_numbers', 'p', 'p.is_primary = 1')
     *          ->execute();
     *
     * @param non-empty-string $from The alias that points to a from clause.
     * @param non-empty-string $join The table name to join.
     * @param non-empty-string $alias The alias of the join table.
     * @param ?non-empty-string $condition The condition for the join.
     */
    public function leftJoin(string $from, string $join, string $alias, null|string $condition = null): static;

    /**
     * Creates and adds a right join to the query.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.id', 'u.username')
     *          ->from('users', 'u')
     *          ->rightJoin('u', 'phone_numbers', 'p', 'p.is_primary = 1')
     *          ->execute();
     *
     * @param non-empty-string $from The alias that points to a from clause.
     * @param non-empty-string $join The table name to join.
     * @param non-empty-string $alias The alias of the join table.
     * @param ?non-empty-string $condition The condition for the join.
     */
    public function rightJoin(string $from, string $join, string $alias, null|string $condition = null): static;

    /**
     * Specifies a grouping over the results of the query.
     *
     * Replaces any previously specified groupings, if any.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.username')
     *          ->from('users', 'u')
     *          ->groupBy('u.id')
     *          ->execute();
     *
     * @param non-empty-string $expression The grouping expression.
     * @param non-empty-string ...$expressions Additional grouping expressions.
     *
     * @no-named-arguments
     */
    public function groupBy(string $expression, string ...$expressions): static;

    /**
     * Adds a grouping expression to the query.
     *
     * Example:
     *
     *      $users = $database
     *          ->createQueryBuilder()
     *          ->select('u.username')
     *          ->from('users', 'u')
     *          ->groupBy('u.last_login')
     *          ->andGroupBy('u.created_at')
     *          ->execute();
     *
     * @param non-empty-string $expression The grouping expression.
     * @param non-empty-string ...$expressions Additional grouping expressions.
     *
     * @no-named-arguments
     */
    public function andGroupBy(string $expression, string ...$expressions): static;

    /**
     * Specifies a restriction over the groups of the query.
     *
     * Replaces any previous having restrictions, if any.
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $restriction The restriction over the groups.
     */
    public function having(string|Expression\CompositeExpressionInterface $restriction): self;

    /**
     * Adds a restriction over the groups of the query, forming a logical
     * conjunction with any existing having restrictions.
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $restriction The restriction over the groups.
     */
    public function andHaving(string|Expression\CompositeExpressionInterface $restriction): self;

    /**
     * Adds a restriction over the groups of the query, forming a logical
     * disjunction  with any existing having restrictions.
     *
     * @param non-empty-string|Expression\CompositeExpressionInterface $restriction The restriction over the groups.
     */
    public function orHaving(string|Expression\CompositeExpressionInterface $restriction): self;

    /**
     * Specifies an ordering for the query results.
     *
     * Replaces any previously specified orderings, if any.
     *
     * @param non-empty-string $sort The ordering expression.
     * @param OrderDirection $direction The ordering direction.
     */
    public function orderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static;

    /**
     * Adds an ordering to the query results.
     *
     * @param non-empty-string $sort The ordering expression.
     * @param OrderDirection $direction The ordering direction.
     */
    public function andOrderBy(string $sort, OrderDirection $direction = OrderDirection::Ascending): static;

    /**
     * Sets the position of the first result to retrieve.
     *
     * @param int<0, max> $offset
     */
    public function offset(int $offset): static;

    /**
     * Sets the maximum number of results to retrieve.
     *
     * @param int<0, max> $limit
     */
    public function limit(int $limit): static;

    /**
     * Fetch one row, where columns are index using their names.
     *
     * @param array<non-empty-string, mixed> $parameters - Optional query parameters.
     *
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     * @throws Exception\LogicException If the query state is not valid.
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     *
     * @return null|array<non-empty-string, mixed>
     */
    public function fetchOneAssociative(array $parameters = []): null|array;

    /**
     * Fetch one row, where columns are index using numeric values.
     *
     * @param array<non-empty-string, mixed> $parameters - Optional query parameters.
     *
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, or $order_by contains invalid values.
     * @throws Exception\LogicException If the query state is not valid.
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     *
     * @return null|list<mixed>
     */
    public function fetchOneNumeric(array $parameters = []): null|array;

    /**
     * Fetch one, or more rows, where columns are index using their names.
     *
     * @param array<non-empty-string, mixed> $parameters - Optional query parameters.
     *
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     * @throws Exception\LogicException If the query state is not valid.
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     *
     * @return list<array<non-empty-string, mixed>>
     */
    public function fetchAllAssociative(array $parameters = []): array;

    /**
     * Fetch one, or more rows, where columns are index using numeric values.
     *
     * @param array<non-empty-string, mixed> $parameters - Optional query parameters.
     *
     * @throws Exception\ConnectionException If the connection to the database is lost.
     * @throws Exception\InvalidQueryException If the operation fails due to an invalid query (such as a syntax error).
     * @throws Exception\InvalidArgumentException If $fields is empty, $order_by contains invalid values, or $offset, or $limit are negative.
     * @throws Exception\LogicException If the query state is not valid.
     * @throws Exception\RuntimeException If the operation fails due to unexpected condition.
     *
     * @return list<list<mixed>>
     */
    public function fetchAllNumeric(array $parameters = []): array;
}
