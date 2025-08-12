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

use Neu\Component\Console\Formatter\Formatter;
use Neu\Component\Console\Formatter\FormatterInterface;
use Psl\Html;

abstract class AbstractOutput implements OutputInterface
{
    /**
     * The global verbosity level for the `Output`.
     */
    protected Verbosity $verbosity;

    /**
     * The formatter instance.
     */
    protected FormatterInterface $formatter;

    /**
     * The output cursor.
     */
    protected null|Cursor $cursor = null;

    /**
     * Construct a new `Output` object.
     */
    public function __construct(Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, null|FormatterInterface $formatter = null)
    {
        $this->verbosity = $verbosity;
        $this->formatter = $formatter ?? new Formatter($decorated);

        if (null !== $decorated) {
            $this->setDecorated($decorated);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        if (!$this->shouldOutput($verbosity)) {
            return;
        }

        $this->doWrite($this->format($message, $type));
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    final public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->write($message . OutputInterface::END_OF_LINE, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getCursor(): Cursor
    {
        if ($this->cursor === null) {
            $this->cursor = new Cursor($this);
        }

        return $this->cursor;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getVerbosity(): Verbosity
    {
        return $this->verbosity;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setVerbosity(Verbosity $verbosity): self
    {
        $this->verbosity = $verbosity;

        return $this;
    }

    /**
     * Determine how the given verbosity compares to the class's verbosity level.
     */
    protected function shouldOutput(Verbosity $verbosity): bool
    {
        return ($verbosity->value <= $this->verbosity->value);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function format(string $message, Type $type = Type::Normal): string
    {
        return match ($type) {
            Type::Raw => $message,
            Type::Normal => $this->formatter->format($message),
            Type::Plain => Html\strip_tags($message),
        };
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isDecorated(): bool
    {
        return $this->getFormatter()->isDecorated();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setDecorated(bool $decorated): self
    {
        $this->getFormatter()->setDecorated($decorated);

        return $this;
    }

    /**
     * Write the given content to the output.
     */
    abstract protected function doWrite(string $content): void;
}
