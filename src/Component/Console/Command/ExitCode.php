<?php

declare(strict_types=1);

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
