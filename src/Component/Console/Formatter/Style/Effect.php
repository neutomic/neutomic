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

enum Effect: string
{
    case Bold = '1';
    case Underline = '4';
    case Blink = '5';
    case Reverse = '7';
    case Conceal = '8';
}
