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

use Amp\Postgres;
use Neu\Component\Database\Database;
use Psl\Env;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

$config = Postgres\PostgresConfig::fromString('host=127.0.0.1 port=5432 user=main password=main');

if (Env\get_var('POOL')) {
    IO\write_error_line('Using connection pool');

    $connection = new Postgres\PostgresConnectionPool($config);
} else {
    IO\write_error_line('Using single connection');

    $connection = Postgres\connect($config);
}

return new Database($connection);
