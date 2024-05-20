<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Amp\ByteStream;
use Neu\Component\Console\Formatter\FormatterInterface;

final class ByteStreamConsoleOutput extends AbstractConsoleOutput
{
    /**
     * Construct a new {@see ByteStreamConsoleOutput} object.
     */
    public function __construct(ByteStream\WritableStream $standardOutputStream, ByteStream\WritableStream $errorOutputStream, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?FormatterInterface $formatter = null)
    {
        $standardOutput = new ByteStreamOutput($standardOutputStream, $verbosity, $decorated, $formatter);
        $standardErrorOutput = new ByteStreamOutput($errorOutputStream, $verbosity, $decorated, $formatter);

        parent::__construct($standardOutput, $standardErrorOutput, $decorated);
    }
}
