<?php

declare(strict_types=1);

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
