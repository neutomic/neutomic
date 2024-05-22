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

use Neu\Component\Console\Formatter\Formatter;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Type;
use Neu\Component\Console\Output\Verbosity;
use Psl\Str;

final readonly class SectionBlock implements BlockInterface
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
        $this->output->writeLine('', $verbosity);
        $this->output->writeLine(Str\format('<color="bright-yellow">%s</>', Formatter::escapeTrailingBackslash($message)), $verbosity);

        /** @var int<0, max> $width */
        $width = Str\width($this->output->format($message, Type::Plain));

        $this->output->writeLine(Str\format('<bold color="bright-yellow">%s</>', Str\repeat('─', $width)), $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
