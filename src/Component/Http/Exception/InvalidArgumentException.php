<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use InvalidArgumentException as RootInvalidArgumentException;

class InvalidArgumentException extends RootInvalidArgumentException implements ExceptionInterface
{
}
