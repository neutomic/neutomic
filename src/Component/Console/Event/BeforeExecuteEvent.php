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
 * Allows doing things before the command is executed, like skipping the command or changing the input.
 */
final readonly class BeforeExecuteEvent extends Event
{
    /**
     * Indicates if the command should be run or skipped.
     */
    public bool $commandShouldRun;

    public function __construct(InputInterface $input, OutputInterface $output, bool $commandShouldRun = true)
    {
        parent::__construct($input, $output);

        $this->commandShouldRun = $commandShouldRun;
    }

    /**
     * Returns a new instance with the given command should run value.
     */
    public function withCommandShouldRun(bool $commandShouldRun): self
    {
        return new self($this->input, $this->output, $commandShouldRun);
    }
}
