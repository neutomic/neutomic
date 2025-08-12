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

namespace Neu\Component\Console\UserInput;

use Psl\Iter;
use Psl\Str;
use Psl\Vec;

/**
 * The {@see MenuUserInput} class presents the user with a prompt and a list of available
 * options to choose from.
 *
 * @extends AbstractUserInput<non-empty-string>
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class MenuUserInput extends AbstractUserInput
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function prompt(string $message): string
    {
        $keys = Vec\keys($this->acceptedValues);
        $values = Vec\values($this->acceptedValues);

        $cursor = null;
        if ($this->position !== null) {
            [$column, $row] = $this->position;
            $cursor = $this->output->getCursor();
            $cursor->save();
            $cursor->move($column, $row);
        }

        $this->output->writeLine(Str\format('<fg=green>%s</>', $message));
        $this->output->writeLine('');
        foreach ($values as $index => $item) {
            if ($this->default === $keys[$index]) {
                $this->output->writeLine(Str\format('  [<fg=yellow>%d</>] %s (default)', $index + 1, $item));
                continue;
            }

            $this->output->writeLine(Str\format('  [<fg=yellow>%d</>] %s', $index + 1, $item));
        }

        if (!$this->input->isInteractive() && null !== $this->default) {
            $result = $this->acceptedValues[$this->default];
        } else {
            $this->output->writeLine('');
            $result = $this->selection($keys);
        }

        $cursor?->restore();

        return $result;
    }

    /**
     * @param array<int, non-empty-string> $keys
     *
     * @return non-empty-string
     */
    private function selection(array $keys): string
    {
        $this->output->write('<fg=green>↪</> ');
        $input = $this->input->getUserInput();
        if (($input === '') && null !== $this->default) {
            return $this->default;
        }

        $input = Str\to_int($input);
        if ($input !== null) {
            $input--;

            if (Iter\contains_key($keys, $input)) {
                return $keys[$input];
            }

            if ($input < 0 || $input >= Iter\count($keys)) {
                $this->output->writeLine('<fg=red>Invalid menu selection</>');
            }
        } else {
            $this->output->writeLine('<fg=red>Invalid menu selection.</>');
        }

        return $this->selection($keys);
    }
}
