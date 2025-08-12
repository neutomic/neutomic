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

namespace Neu\Component\Console\Feedback;

use Neu\Component\Console\Terminal;
use Psl\Iter;
use Psl\Str;

/**
 * The {@see CycleFeedback} class displays feedback by cycling through a series of characters.
 */
final class CycleFeedback extends AbstractFeedback
{
    /**
     * Characters used in displaying the feedback in the output.
     *
     * @var list<string>
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

    /**
     * The character to display when the feedback is finished.
     *
     * @var null|non-empty-string
     */
    private null|string $finishCharacter = null;


    /**
     * Set the character to display when the feedback is finished.
     *
     * @param non-empty-string $character
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
    #[\Override]
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
    #[\Override]
    protected function setMaxLength(): self
    {
        parent::setMaxLength();
        if (null === $this->finishCharacter) {
            return $this;
        }

        /** @var int<1, max> $finishCharacterLength */
        $finishCharacterLength = Str\length($this->finishCharacter);
        if ($finishCharacterLength > $this->maxLength) {
            $this->maxLength = $finishCharacterLength;
        }

        return $this;
    }
}
