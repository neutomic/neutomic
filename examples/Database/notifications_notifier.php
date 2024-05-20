<?php

declare(strict_types=1);

namespace Neu\Examples\Database;

use Neu\Component\Database\DatabaseInterface;
use Psl\IO;
use Psl\Str;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$channel = 'test';
$notifier = $database->getNotifier($channel);

IO\write_line('notifying "test" channel.');

$input = IO\input_handle();
while (true) {
    IO\write('> ');

    $message = $input->read();
    $message = Str\strip_suffix($message, "\n");

    $notifier->notify($message);
}
