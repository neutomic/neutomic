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
use Throwable;

/**
 * Allows handling throwables thrown while running a command.
 */
final readonly class ThrowableEvent extends Event
{
    public Throwable $throwable;
    public null|int $exitCode;

    public function __construct(InputInterface $input, OutputInterface $output, Throwable $throwable, null|int $exitCode = null)
    {
        parent::__construct($input, $output);

        $this->throwable = $throwable;
        $this->exitCode = $exitCode;
    }

    /**
     * Returns a new instance with the given throwable.
     */
    public function withThrowable(Throwable $throwable): self
    {
        return new self($this->input, $this->output, $throwable, $this->exitCode);
    }

    /**
     * Returns a new instance with the given exit code.
     */
    public function withExitCode(int $exitCode): self
    {
        return new self($this->input, $this->output, $this->throwable, $exitCode);
    }
}
