<?php

declare(strict_types=1);

namespace Neu\Framework\Exception;

use RuntimeException as RootRuntimeException;

final class RuntimeException extends RootRuntimeException implements ExceptionInterface
{
}
