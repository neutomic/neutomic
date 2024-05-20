<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use RuntimeException;

/**
 * Exception thrown when an invalid number of commands is passed into the
 * application.
 */
final class InvalidNumberOfCommandsException extends RuntimeException implements ExceptionInterface
{
}
