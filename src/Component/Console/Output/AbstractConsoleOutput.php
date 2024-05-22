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

abstract class AbstractConsoleOutput implements ConsoleOutputInterface
{
    /**
     * Standard output.
     */
    private OutputInterface $standardOutput;

    /**
     * Standard error output.
     */
    private OutputInterface $standardErrorOutput;

    /**
     * Construct a new {@see AbstractConsoleOutput} object.
     */
    public function __construct(OutputInterface $standardOutput, OutputInterface $standardErrorOutput, null|bool $decorated = null)
    {
        $this->standardOutput = $standardOutput;
        $this->standardErrorOutput = $standardErrorOutput;

        if (null === $decorated) {
            $this->setDecorated(
                $this->standardOutput->isDecorated() && $this->standardErrorOutput->isDecorated()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function format(string $message, Type $type = Type::Normal): string
    {
        return $this->standardOutput->format($message, $type);
    }

    /**
     * @inheritDoc
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->standardOutput->write($message, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->standardOutput->writeLine($message, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
    public function getCursor(): Cursor
    {
        return $this->standardOutput->getCursor();
    }

    /**
     * @inheritDoc
     */
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->standardOutput->setFormatter($formatter);
        $this->standardErrorOutput->setFormatter($formatter);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFormatter(): FormatterInterface
    {
        return $this->standardOutput->getFormatter();
    }

    /**
     * @inheritDoc
     */
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->standardOutput->setVerbosity($verbosity);
        $this->standardErrorOutput->setVerbosity($verbosity);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVerbosity(): Verbosity
    {
        return $this->standardOutput->getVerbosity();
    }

    /**
     * @inheritDoc
     */
    public function getErrorOutput(): OutputInterface
    {
        return $this->standardErrorOutput;
    }

    /**
     * @inheritDoc
     */
    public function isDecorated(): bool
    {
        return $this->standardOutput->isDecorated();
    }

    /**
     * @inheritDoc
     */
    public function setDecorated(bool $decorated): self
    {
        $this->standardOutput->setDecorated($decorated);
        $this->standardErrorOutput->setDecorated($decorated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStream(): mixed
    {
        return $this->standardOutput->getStream();
    }
}
