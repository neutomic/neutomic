<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Neu\Component\Console\Formatter\FormatterInterface;

use const PHP_EOL;

interface OutputInterface
{
    public const string TAB = "\t";
    public const string END_OF_LINE = PHP_EOL;
    public const string CTRL = "\r";

    /**
     * Format contents by parsing the style tags and applying necessary formatting.
     */
    public function format(string $message, Type $type = Type::Normal): string;

    /**
     * Send output to the standard output stream.
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void;

    /**
     * Send output to the standard output stream with a new line character appended to the message.
     */
    public function writeLine(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void;

    /**
     * Get the output cursor.
     */
    public function getCursor(): Cursor;

    /**
     * Set the formatter instance.
     */
    public function setFormatter(FormatterInterface $formatter): self;

    /**
     * Returns the formatter instance.
     */
    public function getFormatter(): FormatterInterface;

    /**
     * Set the global verbosity of the `Output`.
     */
    public function setVerbosity(Verbosity $verbosity): self;

    /**
     * Get the global verbosity of the `Output`.
     */
    public function getVerbosity(): Verbosity;

    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated(): bool;

    /**
     * Set the decorated flag.
     */
    public function setDecorated(bool $decorated): self;

    /**
     * Get the stream resource.
     *
     * @return resource|null The stream resource or null if not available
     */
    public function getStream(): mixed;
}
