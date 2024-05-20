<?php

declare(strict_types=1);

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
    protected ?Cursor $cursor = null;

    /**
     * Construct a new `Output` object.
     */
    public function __construct(Verbosity $verbosity = Verbosity::Normal, null|bool $decorated = null, ?FormatterInterface $formatter = null)
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
    final public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->write($message . OutputInterface::END_OF_LINE, $verbosity, $type);
    }

    /**
     * @inheritDoc
     */
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
    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * @inheritDoc
     */
    public function setFormatter(FormatterInterface $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getVerbosity(): Verbosity
    {
        return $this->verbosity;
    }

    /**
     * @inheritDoc
     */
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
    public function isDecorated(): bool
    {
        return $this->getFormatter()->isDecorated();
    }

    /**
     * @inheritDoc
     */
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
