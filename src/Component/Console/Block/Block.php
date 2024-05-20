<?php

declare(strict_types=1);

namespace Neu\Component\Console\Block;

use Neu\Component\Console\Formatter\Formatter;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Type;
use Neu\Component\Console\Output\Verbosity;
use Neu\Component\Console\Terminal;
use Psl\Str;
use Psl\Vec;

readonly class Block implements BlockInterface
{
    private OutputInterface $output;
    private ?string $type ;
    private ?string $style ;
    private string $prefix ;
    private bool $padding;
    private bool $escape ;
    private bool $indent ;

    public function __construct(OutputInterface $output, ?string $type = null, ?string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = false, bool $indent = false)
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
     */
    public function withType(?string $type): self
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
     */
    public function withStyle(?string $style): self
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

            if ($style) {
                $line = Str\format('<%s>%s</>', $style, $line);
            }


            $this->output->writeLine($line, $verbosity);
        }
        $this->output->writeLine('', $verbosity);

        return $this;
    }
}
