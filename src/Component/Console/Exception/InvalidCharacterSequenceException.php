<?php

declare(strict_types=1);

namespace Neu\Component\Console\Exception;

use RuntimeException;

/**
 * Exception thrown when an invalid character sequence is used in a `Feedback`
 * class.
 */
final class InvalidCharacterSequenceException extends RuntimeException implements ExceptionInterface
{
}
