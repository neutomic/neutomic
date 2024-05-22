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

use Neu\Component\Console\Formatter\FormatterInterface;
use Psl\IO;

final class HandleConsoleOutput extends AbstractConsoleOutput
{
    /**
     * Construct a new {@see HandleConsoleOutput} object.
     */
    public function __construct(IO\WriteHandleInterface $standardOutputHandle, IO\WriteHandleInterface $standardErrorOutputHandle, Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, null|FormatterInterface $formatter = null)
    {
        $standardOutput = new HandleOutput($standardOutputHandle, $verbosity, $decorated, $formatter);
        $standardErrorOutput = new HandleOutput($standardErrorOutputHandle, $verbosity, $decorated, $formatter);

        parent::__construct($standardOutput, $standardErrorOutput, $decorated);
    }
}
