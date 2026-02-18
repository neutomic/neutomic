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
use Psl\Async;
use Psl\IO;

/** @var DatabaseInterface $database */
$database = require __DIR__ . '/bootstrap.php';

$listener = $database->getListener('test');

Async\Scheduler::onSignal(SIGINT, $listener->close(...));

foreach ($listener->listen() as $notification) {
    IO\write_line('notification received from process #%d: "%s"', $notification->pid, $notification->payload);
}
