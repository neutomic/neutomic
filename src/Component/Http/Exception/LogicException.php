<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use LogicException as RootLogicException;

class LogicException extends RootLogicException implements ExceptionInterface
{
}
