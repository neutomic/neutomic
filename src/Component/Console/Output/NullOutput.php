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
use Neu\Component\Console\Formatter\NullFormatter;

final class NullOutput extends AbstractOutput implements ConsoleOutputInterface
{
    public function __construct()
    {
        parent::__construct(Verbosity::Quite, false, new NullFormatter());
    }

    /**
     * @inheritDoc
     */
    public function setFormatter(FormatterInterface $formatter): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVerbosity(): Verbosity
    {
        return Verbosity::Quite;
    }

    /**
     * @inheritDoc
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getErrorOutput(): OutputInterface
    {
        return new NullOutput();
    }

    /**
     * @inheritDoc
     */
    protected function doWrite(string $content): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getStream(): null
    {
        return null;
    }
}
