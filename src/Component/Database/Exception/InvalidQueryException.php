<?php

declare(strict_types=1);

namespace Neu\Component\Database\Exception;

use InvalidArgumentException;

final class InvalidQueryException extends InvalidArgumentException implements ExceptionInterface
{
}
