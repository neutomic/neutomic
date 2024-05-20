<?php

declare(strict_types=1);

namespace Neu\Component\Console\UserInput;

use Psl\Iter;
use Psl\Str;

/**
 * @extends AbstractUserInput<bool>
 */
final class ConfirmationUserInput extends AbstractUserInput
{
    /**
     * The message to be appended to the prompt message containing the accepted
     * values.
     */
    protected string $message = ' [y/n]: ';
    /**
     * Input values accepted to continue.
     *
     * @var array<string, bool>
     */
    protected array $acceptedValues = [
        '1' => true,
        'y' => true,
        'yes' => true,
        '0' => false,
        'n' => false,
        'no' => false,
    ];

    /**
     * @inheritDoc
     */
    public function prompt(string $message): bool
    {
        $cursor = null;
        if ($this->position !== null) {
            $cursor = $this->output->getCursor();
            $cursor->save();
            [$column, $row] = $this->position;
            $cursor->move($column, $row);
        }

        $this->output->write($message . ' ' . $this->message);
        if ($this->default !== '' && !$this->input->isInteractive()) {
            $input = $this->default;
        } else {
            $input = Str\lowercase($this->input->getUserInput());
        }

        if ('' === $input && null !== $this->default) {
            $input = $this->default;
        }

        if (!Iter\contains_key($this->acceptedValues, $input)) {
            return $this->prompt($message);
        }

        $cursor?->restore();
        $this->output->writeLine('');

        return $this->acceptedValues[$input];
    }

    /**
     * @inheritDoc
     */
    public function setDefault(?string $default): self
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
