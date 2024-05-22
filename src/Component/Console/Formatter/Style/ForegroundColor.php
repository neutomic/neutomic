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

namespace Neu\Component\Console\Formatter\Style;

enum ForegroundColor: string
{
    case Black = '30';
    case Red = '31';
    case Green = '32';
    case Yellow = '33';
    case Blue = '34';
    case Magenta = '35';
    case Cyan = '36';
    case White = '37';
    case Gray = '90';
    case BrightRed = '91';
    case BrightGreen = '92';
    case BrightYellow = '93';
    case BrightBlue = '94';
    case BrightMagenta = '95';
    case BrightCyan = '96';
    case BrightWhite = '97';
    case Default = '39';
}
