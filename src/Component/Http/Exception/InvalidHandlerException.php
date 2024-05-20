<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Runtime\Handler\HandlerInterface;

final class InvalidHandlerException extends InvalidArgumentException
{
    public static function forHandler(mixed $handler): self
    {
        return new self(
            'Invalid handler provided. Expected an instance of ' . HandlerInterface::class . ', got ' . get_debug_type($handler) . '.',
        );
    }
}
