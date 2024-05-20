<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Neu\Component\Console\Formatter\FormatterInterface;
use Psl\IO;

final class HandleConsoleOutput extends AbstractConsoleOutput
{
    /**
     * Construct a new {@see HandleConsoleOutput} object.
     */
    public function __construct(IO\WriteHandleInterface $standardOutputHandle, IO\WriteHandleInterface $standardErrorOutputHandle, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?FormatterInterface $formatter = null)
    {
        $standardOutput = new HandleOutput($standardOutputHandle, $verbosity, $decorated, $formatter);
        $standardErrorOutput = new HandleOutput($standardErrorOutputHandle, $verbosity, $decorated, $formatter);

        parent::__construct($standardOutput, $standardErrorOutput, $decorated);
    }
}
