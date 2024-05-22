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
 * Allows to manipulate the exit code of the application after a command has been executed.
 */
final readonly class AfterExecuteEvent extends Event
{
    public int $exitCode;

    public function __construct(InputInterface $input, OutputInterface $output, int $exitCode)
    {
        parent::__construct($input, $output);

        $this->exitCode = $exitCode;
    }

    public function withExitCode(int $exitCode): self
    {
        return new self($this->input, $this->output, $exitCode);
    }
}
