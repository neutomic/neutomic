<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Exception;

use InvalidArgumentException as RootInvalidArgumentException;

final class InvalidArgumentException extends RootInvalidArgumentException implements ExceptionInterface
{
}
