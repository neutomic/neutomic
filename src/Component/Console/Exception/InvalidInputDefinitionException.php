<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use RuntimeException;

/**
 * Exception thrown when an `InputDefinition` is requested that hasn't been
 * registered.
 */
final class InvalidInputDefinitionException extends RuntimeException implements ExceptionInterface
{
}
