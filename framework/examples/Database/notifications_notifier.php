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
