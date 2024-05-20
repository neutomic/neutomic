<?php

declare(strict_types=1);

namespace Neu\Component\Database\Exception;

use LogicException as RootLogicException;

final class LogicException extends RootLogicException implements ExceptionInterface
{
}
