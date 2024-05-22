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

use Neu\Component\Database\DatabasePoolInterface;
use Psl\Async;
use Psl\IO;
use Psl\Ref;

const BATCH_SIZE = 100;
const BATCH_COUNT = 5;

/** @var DatabasePoolInterface $connection */
$connection = require __DIR__ . '/bootstrap.php';

$connection->query('DROP TABLE IF EXISTS users');
$connection->query('CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, name VARCHAR(255))');

$promises = [];

$inserts = new Ref(0);
$selects = new Ref(0);
$updates = new Ref(0);
$deletes = new Ref(0);

for ($y = 0; $y < BATCH_COUNT; $y++) {
    $name = static function (int $i, string $suffix = '') use ($y): string {
        return $y . 'user' . $i . $suffix;
    };

    $promises[] = Async\run(static function () use ($connection, $inserts, $selects, $updates, $deletes, $y, $name) {
        IO\write_line('[INSERT][%d] started', $y);
        $promises = [];
        for ($i = 0; $i < BATCH_SIZE; $i++) {
            $promises[] = Async\run(static function () use ($connection, $name, $inserts, $y, $i) {
                $connection->insert('users', [
                    'name' => $name($i)
                ]);

                IO\write_line('[INSERT][%d] user %d', $y, $i);

                $inserts->value++;
            });
        }

        Async\all($promises);
        IO\write_line('[INSERT][%d] finished', $y);

        IO\write_line('[SELECT][%d] started', $y);
        $promises = [];
        for ($i = 0; $i < BATCH_SIZE; $i++) {
            $promises[] = Async\run(static function () use ($connection, $name, $selects, $y, $i) {
                $connection
                    ->createQueryBuilder()
                    ->select('*')
                    ->from('users')
                    ->where('user = ?')
                    ->execute([$name($i)]);

                IO\write_line('[SELECT][%d] user %d', $y, $i);

                $selects->value++;
            });
        }
        Async\all($promises);
        IO\write_line('[SELECT][%d] finished', $y);

        IO\write_line('[UPDATE][%d] started', $y);
        $promises = [];
        for ($i = 0; $i < BATCH_SIZE; $i++) {
            $promises[] = Async\run(static function () use ($connection, $name, $updates, $y, $i) {
                $connection
                    ->createQueryBuilder()
                    ->update('users')
                    ->set('name', '?')
                    ->where('name = ?')
                    ->execute([
                        $name($i, '_updated'),
                        $name($i),
                    ]);

                IO\write_line('[UPDATE][%d] user %d', $y, $i);

                $updates->value++;
            });
        }
        Async\all($promises);
        IO\write_line('[UPDATE][%d] finished', $y);

        IO\write_line('[DELETE][%d] started', $y);
        $promises = [];
        for ($i = 0; $i < BATCH_SIZE; $i++) {
            $promises[] = Async\run(static function () use ($connection, $name, $deletes, $y, $i) {
                $connection
                    ->createQueryBuilder()
                    ->delete('users')
                    ->where('name = ?')
                    ->execute([
                        $name($i, '_updated')
                    ]);

                IO\write_line('[DELETE][%d] user %d', $y, $i);

                $deletes->value++;
            });
        }
        Async\all($promises);
        IO\write_line('[DELETE][%d] finished', $y);
    });
}


Async\all($promises);
