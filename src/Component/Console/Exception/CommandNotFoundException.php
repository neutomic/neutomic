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

namespace Neu\Component\Console\Exception;

use Neu\Component\Console\Command\ExitCode;
use Override;

/**
 * Exception thrown when an invalid command name is provided to the application.
 */
final class CommandNotFoundException extends RuntimeException implements ConsoleExceptionInterface
{
    #[Override]
    public function getExitCode(): ExitCode
    {
        return ExitCode::CommandNotFound;
    }
}
