<?php

declare(strict_types=1);

namespace Neu\Component\Console\Output;

use Neu\Component\Console\Exception\InvalidArgumentException;
use Neu\Component\Console\Output\Sequence\Erase;
use Psl\Str;

/**
 * @see http://ascii-table.com/ansi-escape-sequences.php
 */
final readonly class Cursor
{
    public function __construct(private OutputInterface $output)
    {
    }

    /**
     * Erase.
     */
    public function erase(Erase $mode = Erase::Line, Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->output->write($mode->value, $verbosity);
    }

    /**
     * Move the cursor to the home position in the upper-left corner of the screen (line 0, column 0).
     */
    public function home(Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->move(0, 0, $verbosity);
    }

    /**
     * Moves the cursor to the specified position (coordinates).
     */
    public function move(int $column, int $row, Verbosity $verbosity = Verbosity::Normal): void
    {
        if ($row < 0 || $column < 0) {
            throw new InvalidArgumentException('Invalid coordinates.');
        }

        $this->output->write(Str\format("\033[%d;%dH", $row + 1, $column), $verbosity);
    }

    /**
     * Hide the cursor from the terminal.
     */
    public function hide(Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->output->write("\033[?25l", $verbosity);
    }

    /**
     * Show the cursor.
     */
    public function show(Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->output->write("\033[?25h", $verbosity);
    }

    /**
     * Save the current cursor position.
     */
    public function save(Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('s', $verbosity);
    }

    /**
     * Restore the cursor to its previous position.
     */
    public function restore(Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('u', $verbosity);
    }

    /**
     * Move the cursor $n times up.
     */
    public function up(int $n = 1, Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('A', $verbosity, $n);
    }

    /**
     * Move the cursor $n times down.
     */
    public function down(int $n = 1, Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('B', $verbosity, $n);
    }

    /**
     * Move the cursor $n times forward.
     */
    public function forward(int $n = 1, Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('C', $verbosity, $n);
    }

    /**
     * Move the cursor $n times backward.
     */
    public function backward(int $n = 1, Verbosity $verbosity = Verbosity::Normal): void
    {
        $this->sequence('D', $verbosity, $n);
    }

    private function sequence(string $sequence, Verbosity $verbosity = Verbosity::Normal, ?int $n = null): void
    {
        if ($n !== null) {
            if ($n < 0) {
                throw new InvalidArgumentException(
                    'Expected $n to be a positive integer.',
                );
            }

            $this->output->write(Str\format("\033[%d%s", $n, $sequence), $verbosity);
        } else {
            $this->output->write(Str\format("\033[%s", $sequence), $verbosity);
        }
    }
}
