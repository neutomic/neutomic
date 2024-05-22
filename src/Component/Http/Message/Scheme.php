<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Http\Message;

enum Scheme: string
{
    case Http = 'http';
    case Https = 'https';

    public static function fromPort(int $port): null|self
    {
        return match ($port) {
            80 => self::Http,
            443 => self::Https,
            default => null,
        };
    }
}
