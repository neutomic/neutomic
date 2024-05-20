<?php

declare(strict_types=1);

namespace Neu\Component\Console\Feedback;

use Neu\Component\Console\Terminal;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

/**
 * The `CycleFeedback` class displays feedback by cycling through a series of characters.
 */
final class CycleFeedback extends AbstractFeedback
{
    /**
     * @inheritDoc
     */
    protected array $characterSequence = [
        '-',
        '\\',
        '|',
        '/',
    ];

    /**
     * @inheritDoc
     */
    protected string $prefix = '{:message} ';

    /**
     * @inheritDoc
     */
    protected string $suffix = '';

    private ?string $finishCharacter = null;


    /**
     * Set the character to display when the feedback is finished.
     *
     * @var non-empty-string $character
     */
    public function setFinishCharacter(string $character): self
    {
        $this->finishCharacter = $character;
        $this->setMaxLength();

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function display(bool $finish = false): void
    {
        $variables = $this->buildOutputVariables();
        $index = $this->current % Iter\count($this->characterSequence);
        $feedback = $this->characterSequence[$index];
        $feedback = Str\pad_right($feedback, $this->maxLength + 1);

        $prefix = $this->insert($this->prefix, $variables);
        $suffix = $this->insert($this->suffix, $variables);
        if (!$this->output->isDecorated()) {
            return;
        }

        $variables = [
            'prefix' => $prefix,
            'suffix' => $suffix,
            'feedback' => ($finish && null !== $this->finishCharacter) ? $this->finishCharacter : $feedback,
        ];

        $width = Terminal::getWidth();
        $output = Str\pad_right($this->insert($this->format, $variables), $width);

        $this->print($output, $finish);
    }

    /**
     * Set the maximum length of the available character sequence characters.
     */
    protected function setMaxLength(): self
    {
        parent::setMaxLength();

        $this->maxLength = Math\maxva($this->maxLength, Str\length($this->finishCharacter ?? ''));

        return $this;
    }
}
