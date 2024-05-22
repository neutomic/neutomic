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

use Neu\Component\Console\Exception\InvalidCharacterSequenceException;
use Neu\Component\Console\Output\Type;
use Neu\Component\Console\Terminal;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

/**
 * The {@see ProgressBarFeedback} class displays feedback information with a progress bar.
 * Additional information including percentage done, time elapsed, and time
 * remaining is included by default.
 *
 * @psalm-suppress InvalidOperand
 * @psalm-suppress MissingThrowsDocblock
 */
final class ProgressBarFeedback extends AbstractFeedback
{
    /**
     * The 2-string character format to use when constructing the displayed bar.
     *
     * @var list<string>
     */
    protected array $characterSequence = ['=', '>', ' '];

    /**
     * @inheritDoc
     */
    public function setCharacterSequence(array $characters): static
    {
        if (Iter\count($characters) !== 3) {
            throw new InvalidCharacterSequenceException(
                'Display bar must only contain 3 values',
            );
        }

        return parent::setCharacterSequence($characters);
    }

    /**
     * @inheritDoc
     */
    protected function display(bool $finish = false): void
    {
        if (!$finish && $this->current === $this->total) {
            return;
        }

        $completed = $this->getPercentageComplete();
        $variables = $this->buildOutputVariables();
        if ($finish) {
            $variables['estimated'] = $variables['elapsed'];
        }

        // Need to make prefix and suffix before the bar, so we know how long to render it.
        $prefix = $this->insert($this->prefix, $variables);
        $prefixLength = Str\length(
            $this->output->format($prefix, Type::Plain),
        );

        $suffix = $this->insert($this->suffix, $variables);
        $suffixLength = Str\length(
            $this->output->format($suffix, Type::Plain),
        );

        if (!$this->output->isDecorated()) {
            return;
        }

        $width = Terminal::getWidth();
        $size = $width - ($prefixLength + $suffixLength);
        if ($size < 0) {
            $size = 0;
        }

        /** @var int<0, max> $completed */
        $completed = (int)Math\floor($completed * $size);
        $rest = $size - ($completed + Str\Grapheme\length($this->characterSequence[1]));

        // Str\slice is needed to trim off the bar cap at 100%
        $bar = Str\slice(
            Str\repeat($this->characterSequence[0], $completed) . $this->characterSequence[1] . Str\repeat($this->characterSequence[2], $rest < 0 ? 0 : $rest),
            0,
            $size
        );

        $variables = [
            'prefix' => $prefix,
            'feedback' => $bar,
            'suffix' => $suffix,
        ];

        $output = Str\pad_right($this->insert($this->format, $variables), $width);
        $this->print($output, $finish);
    }
}
