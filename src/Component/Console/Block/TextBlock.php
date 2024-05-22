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

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Verbosity;
use Neu\Component\Console\Terminal;
use Psl\Str;
use Psl\Vec;

/**
 * The {@see TextBlock} class is used to display a block of text in the output.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class TextBlock implements BlockInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @inheritDoc
     */
    public function display(string $message, Verbosity $verbosity = Verbosity::Normal): self
    {
        $message = Str\wrap($message, (int)(((int)(Terminal::getWidth() / 3)) * 2), cut: true);
        $message = Str\join(
            Vec\map(
                Str\split($message, OutputInterface::END_OF_LINE),
                static fn (string $chunk) => '   ' . $chunk
            ),
            OutputInterface::END_OF_LINE,
        );

        $this->output->writeLine('', $verbosity);
        $this->output->writeLine($message, $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
