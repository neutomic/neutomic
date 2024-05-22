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
