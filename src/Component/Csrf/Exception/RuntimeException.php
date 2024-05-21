<?php

declare(strict_types=1);

namespace Neu\Component\Csrf\Exception;

use Neu\Component\Exception\RuntimeException as RootRuntimeException;

class RuntimeException extends RootRuntimeException implements ExceptionInterface
{
}
