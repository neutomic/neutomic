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

enum BackgroundColor: string
{
    case Black = '40';
    case Red = '41';
    case Green = '42';
    case Yellow = '43';
    case Blue = '44';
    case Magenta = '45';
    case Cyan = '46';
    case White = '47';
    case Gray = '100;1';
    case BrightRed = '101';
    case BrightGreen = '102';
    case BrightYellow = '103';
    case BrightBlue = '104';
    case BrightMagenta = '105';
    case BrightCyan = '106';
    case BrightWhite = '107';
    case Default = '49';
}
