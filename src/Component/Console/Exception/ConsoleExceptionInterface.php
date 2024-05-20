<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use Neu\Component\Console\Command\ExitCode;

interface ConsoleExceptionInterface extends ExceptionInterface
{
    /**
     * Retrieve the exit code associated with the exception.
     */
    public function getExitCode(): ExitCode;
}
