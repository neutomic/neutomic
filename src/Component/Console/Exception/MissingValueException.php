<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use RuntimeException;

/**
 * Exception thrown when a value is required and not present. This can be the
 * case with options or arguments.
 */
final class MissingValueException extends RuntimeException implements ExceptionInterface
{
}
