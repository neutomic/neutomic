<?php

declare(strict_types=1);

namespace Neu\Examples\Database;

use Neu\Component\Database\DatabaseInterface;
use Psl\Async;
use Psl\IO;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$listener = $database->getListener('test');

Async\Scheduler::onSignal(SIGINT, $listener->close(...));

foreach ($listener->listen() as $notification) {
    IO\write_line('notification received from process #%d: "%s"', $notification->pid, $notification->payload);
}
