<?php

declare(strict_types=1);

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
        $this->output->writeLine(Str\format('<bold color="bright-yellow">%s</>', Str\repeat('─', Str\width($this->output->format($message, Type::Plain)))), $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
