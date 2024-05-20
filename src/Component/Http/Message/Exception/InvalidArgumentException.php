<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Exception;

use Neu\Component\Http\Exception\InvalidArgumentException as RootInvalidArgumentException;

final class InvalidArgumentException extends RootInvalidArgumentException implements ExceptionInterface
{
}
