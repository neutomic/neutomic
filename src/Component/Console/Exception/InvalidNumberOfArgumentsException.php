<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when parameters are passed in the input that do not belong
 * to registered input definitions.
 */
final class InvalidNumberOfArgumentsException extends InvalidArgumentException implements ExceptionInterface
{
}
