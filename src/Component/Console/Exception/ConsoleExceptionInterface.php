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

interface ConsoleExceptionInterface extends ExceptionInterface
{
    /**
     * Retrieve the exit code associated with the exception.
     */
    public function getExitCode(): ExitCode;
}
