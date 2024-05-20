<?php

declare(strict_types=1);

namespace Neu\Component\Cache\Exception;

use InvalidArgumentException;

final class InvalidValueException extends InvalidArgumentException implements ExceptionInterface
{
}
