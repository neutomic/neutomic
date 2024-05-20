<?php

declare(strict_types=1);

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\Verbosity;

interface BlockInterface
{
    /**
     * Display the block with the given messages.
     */
    public function display(string $message, Verbosity $verbosity = Verbosity::Normal): self;
}
