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
use Override;

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
    #[Override]
    public function format(string $message, Type $type = Type::Normal): string
    {
        return $this->standardOutput->format($message, $type);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->standardOutput->write($message, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->standardOutput->writeLine($message, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getCursor(): Cursor
    {
        return $this->standardOutput->getCursor();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->standardOutput->setFormatter($formatter);
        $this->standardErrorOutput->setFormatter($formatter);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getFormatter(): FormatterInterface
    {
        return $this->standardOutput->getFormatter();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->standardOutput->setVerbosity($verbosity);
        $this->standardErrorOutput->setVerbosity($verbosity);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getVerbosity(): Verbosity
    {
        return $this->standardOutput->getVerbosity();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getErrorOutput(): OutputInterface
    {
        return $this->standardErrorOutput;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isDecorated(): bool
    {
        return $this->standardOutput->isDecorated();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setDecorated(bool $decorated): self
    {
        $this->standardOutput->setDecorated($decorated);
        $this->standardErrorOutput->setDecorated($decorated);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getStream(): mixed
    {
        return $this->standardOutput->getStream();
    }
}
