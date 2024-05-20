<?php

declare(strict_types=1);

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
