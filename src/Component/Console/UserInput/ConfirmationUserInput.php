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
use Override;

/**
 * @extends AbstractUserInput<bool>
 */
final class ConfirmationUserInput extends AbstractUserInput
{
    /**
     * The message to be appended to the prompt message containing the accepted
     * values.
     *
     * @var non-empty-string
     */
    protected string $message = ' [y/n]: ';

    /**
     * Input values accepted to continue.
     *
     * @var array<non-empty-string, bool>
     */
    protected array $acceptedValues = [
        'y' => true,
        'yes' => true,
        'n' => false,
        'no' => false,
    ];

    /**
     * @inheritDoc
     */
    #[Override]
    public function prompt(string $message): bool
    {
        $cursor = null;
        $position = $this->position;
        $this->position = null;
        if ($position !== null) {
            [$column, $row] = $position;

            $cursor = $this->output->getCursor();
            $cursor->save();
            $cursor->move($column, $row);
        }

        try {
            $this->output->write($message . ' ' . $this->message);
            if (!$this->input->isInteractive() && $this->default !== null) {
                $input = $this->default;
            } else {
                $input = Str\lowercase($this->input->getUserInput());
            }

            if ('' === $input && null !== $this->default) {
                $input = $this->default;
            }

            if ('' === $input || !Iter\contains_key($this->acceptedValues, $input)) {
                return $this->prompt($message);
            }

            $this->output->writeLine('');

            return $this->acceptedValues[$input];
        } finally {
            // restore the cursor position
            $this->position = $position;
            $cursor?->restore();
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setDefault(null|string $default): self
    {
        parent::setDefault($default);
        if (null === $default) {
            return $this;
        }

        $default = Str\lowercase($default);
        $message = match ($default) {
            'y', 'yes' => ' [<fg=green;bold;underline>Y</>/n]: ',
            'n', 'no' => ' [y/<fg=green;bold;underline>N</>]: ',
            default => null,
        };

        if (null === $message) {
            return $this;
        }

        $this->message = $message;

        return $this;
    }
}
