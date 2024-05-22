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

$database->query('DROP TABLE IF EXISTS employees');
$database->query('CREATE TABLE IF NOT EXISTS employees (
    id SERIAL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    department INT NOT NULL,
    salary INT NOT NULL
)');

$database
    ->createQueryBuilder()
    ->insert('employees')
    ->values(
        ['name' => ':name1', 'department' => ':department1', 'salary' => ':salary1'],
        ['name' => ':name2', 'department' => ':department2', 'salary' => ':salary2'],
        ['name' => ':name3', 'department' => ':department3', 'salary' => ':salary3'],
        ['name' => ':name4', 'department' => ':department4', 'salary' => ':salary4'],
        ['name' => ':name5', 'department' => ':department5', 'salary' => ':salary5'],
        ['name' => ':name6', 'department' => ':department6', 'salary' => ':salary6'],
        ['name' => ':name7', 'department' => ':department7', 'salary' => ':salary7'],
    )
    ->execute([
        'name1' => 'Jasmine',  'department1' => 2, 'salary1' => 4000,
        'name2' => 'Anna',     'department2' => 1, 'salary2' => 3500,
        'name3' => 'James',    'department3' => 1, 'salary3' => 2500,
        'name4' => 'David',    'department4' => 2, 'salary4' => 5000,
        'name5' => 'Mark',     'department5' => 2, 'salary5' => 3000,
        'name6' => 'Salma',    'department6' => 3, 'salary6' => 4600,
        'name7' => 'Ali',      'department7' => 3, 'salary7' => 3900,
    ]);

$data = $database
    ->createQueryBuilder()
    ->select('e.department', 'AVG(e.salary)')
    ->from('employees', 'e')
    ->groupBy('e.department')
    ->orderBy('e.department', OrderDirection::Descending)
    ->execute()
    ->getRows()
;

foreach ($data as ['department' => $department, 'avg' => $avg]) {
    IO\write_line('Average salary in department number %d, is %d.', $department, $avg);
}

$data = $database
    ->createQueryBuilder()
    ->select('e.department', 'AVG(e.salary)')
    ->from('employees', 'e')
    ->groupBy('e.department')
    ->having(
        $database->createExpressionBuilder()->greaterThan('AVG(e.salary)', '3000')
    )
    ->orderBy('e.department', OrderDirection::Descending)
    ->execute()
    ->getRows()
;

foreach ($data as ['department' => $department, 'avg' => $avg]) {
    IO\write_line('Average salary in department number %d, is %d.', $department, $avg);
}
