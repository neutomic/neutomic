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

namespace Neu\Component\Console\Command;

enum ExitCode: int
{
    /**
     * No error. The command executed successfully.
     */
    case Success = 0;

    /**
     * Catchall for general errors.
     */
    case Failure = 1;

    /**
     * Command has been skipped.
     */
    case SkippedCommand = 113;

    /**
     * Command not found.
     */
    case CommandNotFound = 127;

    /**
     * Command is disabled.
     */
    case CommandDisabled = 133;

    /**
     * The returned exit code is out of range.
     *
     * An exit value greater than 255 returns an exit code modulo 256.
     * For example, exit 3809 gives an exit code of 225 (3809 % 256 = 225).
     */
    case ExitStatusOutOfRange = 255;
}
