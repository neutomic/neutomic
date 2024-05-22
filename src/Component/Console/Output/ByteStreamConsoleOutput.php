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

use Amp\ByteStream;
use Neu\Component\Console\Formatter\FormatterInterface;

final class ByteStreamConsoleOutput extends AbstractConsoleOutput
{
    /**
     * Construct a new {@see ByteStreamConsoleOutput} object.
     */
    public function __construct(ByteStream\WritableStream $standardOutputStream, ByteStream\WritableStream $errorOutputStream, Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, null|FormatterInterface $formatter = null)
    {
        $standardOutput = new ByteStreamOutput($standardOutputStream, $verbosity, $decorated, $formatter);
        $standardErrorOutput = new ByteStreamOutput($errorOutputStream, $verbosity, $decorated, $formatter);

        parent::__construct($standardOutput, $standardErrorOutput, $decorated);
    }
}
