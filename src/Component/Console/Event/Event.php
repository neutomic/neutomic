<?php

declare(strict_types=1);

namespace Neu\Component\Console\Event;

use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;

/**
 * Allows inspecting input and output of a command.
 *
 * @internal
 */
abstract readonly class Event
{
    public InputInterface $input;
    public OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }
}
