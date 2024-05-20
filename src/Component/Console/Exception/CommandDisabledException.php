<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use Neu\Component\Console\Command\ExitCode;
use RuntimeException;

/**
 * Exception thrown when attempting to execute a disabled command.
 */
final class CommandDisabledException extends RuntimeException implements ConsoleExceptionInterface
{
    public function getExitCode(): ExitCode
    {
        return ExitCode::CommandDisabled;
    }
}
