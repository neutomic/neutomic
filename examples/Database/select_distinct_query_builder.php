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
use Psl\PseudoRandom;
use Psl\SecureRandom;
use Psl\Vec;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$database->query('DROP TABLE IF EXISTS users');

$database->query('CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(256) NOT NULL,
    country VARCHAR(256) NOT NULL
)');

$values = [];
$parameters = [];
foreach (['Tunisia', 'France', 'Spain', 'Algeria', 'Egypt', 'United States of America', 'China', 'Japan', 'Brazil', 'South Africa'] as $country) {
    // produce between 10, to 40 users with unique usernames for the current country.
    foreach (Vec\reproduce(PseudoRandom\int(10, 40), static fn () => SecureRandom\string(8)) as $i => $username) {
        $values[] = ['username' => ':username' . $i, 'country' => ':country' . $i];
        $parameters['username'.$i] = $username;
        $parameters['country'.$i] = $country;
    }
}

$database
    ->createQueryBuilder()
    ->insert('users')
    ->values(...$values)
    ->execute($parameters)
;

$countries = $database
    ->createQueryBuilder()
    ->select('u.country')
    ->from('users', 'u')
    ->orderBy('u.country', OrderDirection::Descending)
    ->distinct()
    ->execute()
    ->getRows()
;

foreach ($countries as ['country' => $country]) {
    IO\write_line('- %s', $country);
}
