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

namespace Neu\Component\Console\Output;

enum Verbosity: int
{
    case Quite = 16;
    case Normal = 32;
    case Verbose = 64;
    case VeryVerbose = 128;
    case Debug = 256;

    public function isVerbose(): bool
    {
        return $this->value >= self::Verbose->value;
    }
}
