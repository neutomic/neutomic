<?php

declare(strict_types=1);

namespace Neu\Component\Configuration\Exception;

use UnexpectedValueException;

final class InvalidEntryException extends UnexpectedValueException implements ExceptionInterface
{
}
