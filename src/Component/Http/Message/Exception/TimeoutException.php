<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Exception;

use Neu\Component\Http\Exception\RuntimeException as RootRuntimeException;

final class TimeoutException extends RootRuntimeException implements ExceptionInterface
{
}
