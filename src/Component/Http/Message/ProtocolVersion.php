<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

enum ProtocolVersion: string
{
    case Http10 = '1.0';
    case Http11 = '1.1';
    case Http20 = '2';

    /**
     * Get the major version number.
     *
     * @return 1|2
     */
    public function getMajorVersion(): int
    {
        return match ($this) {
            self::Http10, self::Http11 => 1,
            self::Http20 => 2,
        };
    }
}
