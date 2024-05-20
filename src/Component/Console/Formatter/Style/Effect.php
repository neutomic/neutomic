<?php

declare(strict_types=1);

namespace Neu\Component\Console\Formatter\Style;

enum Effect: string
{
    case Bold = '1';
    case Underline = '4';
    case Blink = '5';
    case Reverse = '7';
    case Conceal = '8';
}
