<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use OutOfBoundsException;

/**
 * Exception thrown when the command used in the application does not exist.
 */
final class InvalidCommandException extends OutOfBoundsException implements ExceptionInterface
{
}
