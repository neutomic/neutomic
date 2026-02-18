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

namespace Neu\Component\Console\Output\Sequence;

/**
 * @see http://ascii-table.com/ansi-escape-sequences.php
 */
enum Erase: string
{
    /**
     * Erase the entire line.
     */
    case Line = "\033[2K";

    /**
     * Erase everything from the cursor to the end of the line.
     */
    case ToEndOfLine = "\033[K";

    /**
     * Erase everything from the cursor to the beginning of the line.
     */
    case ToBeginningOfLine = "\033[1K";

    /**
     * Erase the entire screen.
     */
    case Screen = "\033[2J";

    /**
     * Erase everything from the cursor to the end of the screen.
     */
    case ToEndOfScreen = "\033[0J";

    /**
     * Erase everything from the cursor to the beginning of the screen.
     */
    case ToBeginningOfScreen = "\033[1J";
}
