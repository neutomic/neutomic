<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use Neu\Component\Console\Command\ExitCode;
use RuntimeException;

/**
 * Exception thrown when an invalid command name is provided to the application.
 */
final class CommandNotFoundException extends RuntimeException implements ConsoleExceptionInterface
{
    public function getExitCode(): ExitCode
    {
        return ExitCode::CommandNotFound;
    }
}
