<?php

declare(strict_types=1);

namespace Neu\Component\Console\Recovery;

use Neu\Component\Console\Block\BlockFactoryTrait;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Exception\ConsoleExceptionInterface;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\ConsoleOutputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Verbosity;
use Psl\Str;
use Throwable;

use function array_filter;
use function array_map;
use function dirname;
use function is_string;
use function str_starts_with;

final class Recovery implements RecoveryInterface
{
    use BlockFactoryTrait;

    /**
     * @inheritDoc
     */
    public function recover(InputInterface $input, OutputInterface $output, Throwable $throwable): int
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->renderMessage($output, $throwable);
        $this->renderTrace($output, $throwable);

        if ($throwable instanceof ConsoleExceptionInterface) {
            return $throwable->getExitCode()->value;
        }

        $code = $throwable->getCode();
        if (is_string($code)) {
            $code = Str\to_int($code) ?? ExitCode::Failure->value;
        }

        if ($code > ExitCode::ExitStatusOutOfRange->value) {
            $code %= (ExitCode::ExitStatusOutOfRange->value + 1);
        }

        return $code;
    }

    private function renderMessage(OutputInterface $output, Throwable $throwable): void
    {
        $this
            ->createErrorBlock($output)
            ->withPrefix(' | ')
            ->withType($throwable::class)
            ->display($throwable->getMessage())
        ;
    }

    private function renderTrace(OutputInterface $output, Throwable $throwable): void
    {
        $sourceHighlighted = $this->renderSource($output, $throwable);

        $frames = array_filter(
            array_map(
                static function (array $frame): array {
                    unset($frame['args']);
                    return $frame;
                },
                $throwable->getTrace(),
            ),
            static fn(array $frame): bool => isset($frame['function'], $frame['file']),
        );

        if ([] !== $frames) {
            $output->writeLine(
                '<fg=yellow>Throwable Trace: </>' . OutputInterface::END_OF_LINE,
                Verbosity::VeryVerbose,
            );

            foreach ($frames as $frame) {
                // render user throwables and neu throwables sources in different colors.
                // as the error is usually coming from the user, not neu.
                $file = $frame['file'];
                if ($sourceHighlighted || $this->isNeu($file)) {
                    $traceFormat = ' ↪ <fg=gray;href=%s>%s</>';
                } else {
                    $traceFormat = ' ↪ <fg=bright-red;underline;bold;href=%s>%s</>';
                    $sourceHighlighted = true;
                }

                if (isset($frame['class'])) {
                    $output->writeLine(Str\format('%s%s%s()', $frame['class'], $frame['type'], $frame['function']), Verbosity::VeryVerbose);
                } else {
                    $output->writeLine(Str\format(' %s()', $frame['function']), Verbosity::VeryVerbose);
                }

                $output->writeLine(Str\format($traceFormat, $file, $file . (isset($frame['line']) ? (':' . $frame['line']) : '')), Verbosity::VeryVerbose);
                $output->writeLine('', Verbosity::VeryVerbose);
            }
        }
    }

    private function renderSource(OutputInterface $output, Throwable $throwable): bool
    {
        if (!$this->isNeu($throwable->getFile())) {
            $output->writeLine(Str\format('- <fg=bright-red;underline;bold;href=%1$s>%1$s:%2$d</>', $throwable->getFile(), $throwable->getLine()), Verbosity::Verbose);
            $output->writeLine('', Verbosity::Verbose);

            return true;
        }

        // Render it the same way, but no red or underline:
        $output->writeLine(Str\format('- <fg=gray;href=%1$s>%1$s:%2$d</>', $throwable->getFile(), $throwable->getLine()), Verbosity::Verbose);
        $output->writeLine('', Verbosity::Verbose);

        return false;
    }

    /**
     * Determine if the given file is part of Neu.
     */
    private function isNeu(string $file): bool
    {
        return str_starts_with($file, dirname(__DIR__, 2));
    }
}
