<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Dsn;

use Neu\Component\Broadcast\Exception\InvalidArgumentException;
use Psl\Str;

/**
 * @throws InvalidArgumentException if the DSN is not valid
 */
function from_string(string $path): DsnInterface
{
    if ('' === $path) {
        throw new InvalidArgumentException('Invalid empty DSN');
    }

    if (Str\Byte\starts_with($path, 'unix://')) {
        return Unix::fromString($path);
    }

    if (Str\Byte\starts_with($path, 'tcp://')) {
        return Tcp::fromString($path);
    }

    throw new InvalidArgumentException('Unsupported DSN scheme');
}
