<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Exception;

use Neu\Component\Http\Exception\LogicException as RootLogicException;

final class LogicException extends RootLogicException implements ExceptionInterface
{
}
