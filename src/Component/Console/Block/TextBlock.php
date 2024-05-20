<?php

declare(strict_types=1);

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Verbosity;
use Neu\Component\Console\Terminal;
use Psl\Str;
use Psl\Vec;

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
        $message = Str\wrap($message, (int)((Terminal::getWidth() / 3) * 2), cut: true);
        $message = Str\join(
            Vec\map(
                Str\split($message, OutputInterface::END_OF_LINE),
                static fn(string $chunk) => '   ' . $chunk
            ),
            OutputInterface::END_OF_LINE,
        );

        $this->output->writeLine('', $verbosity);
        $this->output->writeLine($message, $verbosity);
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
