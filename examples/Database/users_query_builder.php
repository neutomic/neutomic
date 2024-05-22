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

namespace Neu\Examples\Database;

use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\OrderDirection;
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

$database
    ->createQueryBuilder()
    ->insert('users')
    ->values(
        ['username' => ':username1', 'email' => ':email1', 'password' => ':password1', 'status' => ':status1'],
        ['username' => ':username2', 'email' => ':email2', 'password' => ':password2', 'status' => ':status2'],
        ['username' => ':username3', 'email' => ':email3', 'password' => ':password3', 'status' => ':status3'],
        ['username' => ':username4', 'email' => ':email4', 'password' => ':password4', 'status' => ':status4'],
        ['username' => ':username5', 'email' => ':email5', 'password' => ':password5', 'status' => ':status5'],
    )
    ->execute([
        'username1' => 'foo', 'email1' => 'foo@example.com', 'password1' => '123456789', 'status1' => 1,
        'username2' => 'bar', 'email2' => 'bar@example.com', 'password2' => '123456789', 'status2' => 2,
        'username3' => 'baz', 'email3' => 'baz@example.com', 'password3' => '123456789', 'status3' => 2,
        'username4' => 'qux', 'email4' => 'qux@example.com', 'password4' => '123456789', 'status4' => 2,
        'username5' => 'dux', 'email5' => 'dux@example.com', 'password5' => '123456789', 'status5' => 2
    ]);

$users = $database
    ->createQueryBuilder()
    ->select('u.username', 'u.email')
    ->from('users', 'u')
    ->execute()
    ->getRows();

assert(count($users) === 5);
foreach ($users as $user) {
    IO\write_line('- username: "%s", email: "%s"', $user['username'], $user['email']);
}

$database
    ->createQueryBuilder()
    ->delete('users', 'u')
    ->where(
        $database->createExpressionBuilder()->equal('u.username', ':username')
    )
    ->execute(['username' => 'dux']);

$users = $database
    ->createQueryBuilder()
    ->select('u.*')
    ->from('users', 'u')
    ->execute()
    ->getRows();

assert(count($users) === 4);
foreach ($users as $user) {
    IO\write_line('- username: "%s", email: "%s", password: "%s"', $user['username'], $user['email'], $user['password']);
}

[$foo] = $database
    ->createQueryBuilder()
    ->select('u.password')
    ->from('users', 'u')
    ->where(
        $database->createExpressionBuilder()->equal('username', ':username')
    )
    ->execute(['username' => 'foo'])
    ->getRows();

IO\write_line('- username: "foo", password: "%s"', $foo['password']);

[['username' => $username, 'email' => $email, 'password' => $password, 'status' => $status]] = $database->createQueryBuilder()
    ->select('u.*')
    ->from('users', 'u')
    ->offset(2)
    ->limit(1)
    ->orderBy('email', OrderDirection::Descending)
    ->execute()
    ->getRows();

assert('baz' === $username);
IO\write_line('- username: "%s", email: "%s", password: "%s", status: %d', $username, $email, $password, $status);
