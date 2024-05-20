<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

enum Scheme: string
{
    case Http = 'http';
    case Https = 'https';

    public static function fromPort(int $port): ?self
    {
        return match ($port) {
            80 => self::Http,
            443 => self::Https,
            default => null,
        };
    }
}
