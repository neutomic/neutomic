<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Exception;

use RuntimeException as RootRuntimeException;

class RuntimeException extends RootRuntimeException implements ExceptionInterface
{
}