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
use Neu\Component\Console\Terminal;
use Psl\Str;
use Psl\Vec;

/**
 * The {@see Block} class is used to display a block of text in the output.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
readonly class Block implements BlockInterface
{
    private OutputInterface $output;

    /**
     * The type of the block.
     *
     * @var non-empty-string|null
     */
    private null|string $type ;

    /**
     * The style of the block.
     *
     * @var non-empty-string|null
     */
    private null|string $style ;

    /**
     * The prefix of the block.
     */
    private string $prefix ;

    /**
     * Whether to add padding to the block.
     */
    private bool $padding;

    /**
     * Whether to escape the message.
     */
    private bool $escape ;

    /**
     * Whether to indent the block.
     */
    private bool $indent ;

    /**
     * Create a new {@see Block} instance.
     *
     * @param OutputInterface $output The output instance to write to.
     * @param non-empty-string|null $type The type of the block.
     * @param non-empty-string|null $style The style of the block.
     * @param string $prefix The prefix of the block.
     * @param bool $padding Whether to add padding to the block.
     * @param bool $escape Whether to escape the message.
     * @param bool $indent Whether to indent the block.
     */
    public function __construct(OutputInterface $output, null|string $type = null, null|string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = false, bool $indent = false)
    {
        $this->output = $output;
        $this->type = $type;
        $this->style = $style;
        $this->prefix = $prefix;
        $this->padding = $padding;
        $this->escape = $escape;
        $this->indent = $indent;
    }

    /**
     * Returns a new instance with the specified type.
     *
     * @param non-empty-string|null $type
     */
    public function withType(null|string $type): self
    {
        return new Block(
            $this->output,
            $type,
            $this->style,
            $this->prefix,
            $this->padding,
            $this->escape,
            $this->indent,
        );
    }

    /**
     * Returns a new instance with the specified style.
     *
     * @param non-empty-string|null $style
     */
    public function withStyle(null|string $style): self
    {
        return new Block(
            $this->output,
            $this->type,
            $style,
            $this->prefix,
            $this->padding,
            $this->escape,
            $this->indent,
        );
    }

    /**
     * Returns a new instance with the specified prefix.
     */
    public function withPrefix(string $prefix): self
    {
        return new Block(
            $this->output,
            $this->type,
            $this->style,
            $prefix,
            $this->padding,
            $this->escape,
            $this->indent,
        );
    }

    /**
     * Returns a new instance with the specified padding.
     */
    public function withPadding(bool $padding): self
    {
        return new Block(
            $this->output,
            $this->type,
            $this->style,
            $this->prefix,
            $padding,
            $this->escape,
            $this->indent,
        );
    }

    /**
     * Returns a new instance with the specified escape.
     */
    public function withEscape(bool $escape): self
    {
        return new Block(
            $this->output,
            $this->type,
            $this->style,
            $this->prefix,
            $this->padding,
            $escape,
            $this->indent,
        );
    }

    /**
     * Returns a new instance with the specified indent.
     */
    public function withIndent(bool $indent): self
    {
        return new Block(
            $this->output,
            $this->type,
            $this->style,
            $this->prefix,
            $this->padding,
            $this->escape,
            $indent,
        );
    }

    /**
     * @inheritDoc
     */
    public function display(string $message, Verbosity $verbosity = Verbosity::Normal): self
    {
        $type = $this->type;
        $style = $this->style;
        $prefix = $this->prefix;
        $padding = $this->padding;
        $escape = $this->escape;
        $indent = $this->indent;

        $width = Terminal::getWidth();
        $indentWidth = 0;
        $lineIndentation = '';
        $prefixWidth = Str\width(
            $this->output->format($prefix, Type::Plain),
        );

        if ($type !== null) {
            $type = Str\format('[%s] ', $type);
            if ($indent) {
                /** @var int<0, max> $indentWidth */
                $indentWidth = Str\width($type);
                $lineIndentation = Str\repeat(' ', $indentWidth);
            }

            $message = $type . $message;
        }

        if ($escape) {
            $message = Formatter::escape($message);
        }

        $lines = Str\split(
            Str\wrap(
                $message,
                $width - $prefixWidth - $indentWidth,
                OutputInterface::END_OF_LINE,
                true,
            ),
            OutputInterface::END_OF_LINE,
        );
        $firstLineIndex = 0;
        if ($padding && $this->output->isDecorated()) {
            $firstLineIndex = 1;
            $lines = Vec\concat([''], $lines);
            $lines[] = '';
        }

        $this->output->writeLine('', $verbosity);
        foreach ($lines as $i => $line) {
            if ($type !== null) {
                $line = $firstLineIndex === $i ? $line : $lineIndentation . $line;
            }

            $line = $prefix . $line;
            $fit = $width -
                Str\width($this->output->format($line, Type::Plain));
            if ($fit > 0) {
                $line .= Str\repeat(' ', $fit);
            }

            if (null !== $style) {
                $line = Str\format('<%s>%s</>', $style, $line);
            }


            $this->output->writeLine($line, $verbosity);
        }
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
