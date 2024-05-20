<?php

declare(strict_types=1);

namespace Neu\Examples\Database;

use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\OrderDirection;
use Neu\Component\Database\TransactionInterface;
use Psl\IO;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$database->query('DROP TABLE IF EXISTS users');
$database->query('CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(1024) NOT NULL,
    password VARCHAR(1024) NOT NULL,
    status INT NOT NULL
)');

$database->transactional(static function(TransactionInterface $transaction): void {
    $transaction->insertAll('users', [
        ['username' => 'foo', 'email' => 'foo@example.com', 'password' => '123456789', 'status' => 1],
        ['username' => 'bar', 'email' => 'bar@example.com', 'password' => '123456789', 'status' => 2],
        ['username' => 'baz', 'email' => 'baz@example.com', 'password' => '123456789', 'status' => 2],
        ['username' => 'qux', 'email' => 'qux@example.com', 'password' => '123456789', 'status' => 2],
        ['username' => 'dux', 'email' => 'dux@example.com', 'password' => '123456789', 'status' => 2],
    ]);
    $users = $transaction->fetchAllAssociative('users', ['username', 'email']);
    assert(count($users) === 5);
    foreach ($users as $user) {
        IO\write_line('- username: "%s", email: "%s"', $user['username'], $user['email']);
    }

    $transaction
        ->createQueryBuilder()
        ->delete('users', 'u')
        ->where(
            $transaction->createExpressionBuilder()->equal('u.username', ':username')
        )
        ->execute(['username' => 'dux']);

    $users = $transaction->fetchAllNumeric('users');
    assert(count($users) === 4);
    foreach ($users as $user) {
        IO\write_line('- username: "%s", email: "%s", password: "%s"', $user[0], $user[1], $user[2]);
    }
    $foo = $transaction->fetchOneAssociative('users', ['password'], criteria: ['username' => 'foo']);
    IO\write_line('- username: "foo", password: "%s"', $foo['password']);
    $bar = $transaction->fetchOneNumeric('users', ['password'], criteria: ['username' => 'bar']);
    IO\write_line('- username: "bar", password: "%s"', $bar[0]);
    [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'status' => $status
    ] = $transaction->fetchAllAssociative('users', offset: 2, limit: 1, order_by: ['email' => OrderDirection::Descending])[0];
    assert('baz' === $username);
    IO\write_line('- username: "%s", email: "%s", password: "%s", status: %d', $username, $email, $password, $status);
});
