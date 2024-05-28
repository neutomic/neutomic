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

/**
 * Exception thrown when an invalid number of commands is passed into the
 * application.
 */
final class InvalidNumberOfCommandsException extends RuntimeException implements ExceptionInterface
{
}
