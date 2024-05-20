<?php

declare(strict_types=1);

namespace Neu\Examples\Database;

use Neu\Component\Cache\Driver\DatabaseDriver;
use Neu\Component\Cache\Store;
use Neu\Component\Database\DatabaseInterface;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';
$driver = new DatabaseDriver($database);
$cache = new Store($driver);

// delete the user cache
$cache->delete('user');

$user = $cache->compute('user', static function(): string {
    return 'foo';
});

assert($user === 'foo');

$user = $cache->compute('user', static function(): string {
    return 'bar';
});

assert($user === 'foo');

$user = $cache->update('user', static function(): string {
    return 'bar';
});

assert($user === 'bar');

$user = $cache->compute('user', static function(): string {
    return 'baz';
});

assert($user === 'bar');

$cache->delete('user');

$user = $cache->compute('user', static function(): string {
    return 'baz';
});

assert($user === 'baz');

echo 'All tests passed!' . PHP_EOL;
