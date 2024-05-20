<?php

declare(strict_types=1);

namespace Neu\Component\Console\Formatter;

use Neu\Component\Console\Formatter\Style\BackgroundColor;
use Neu\Component\Console\Formatter\Style\Effect;
use Neu\Component\Console\Formatter\Style\ForegroundColor;
use Neu\Component\Console\Formatter\Style\Style;
use Neu\Component\Console\Formatter\Style\StyleInterface;
use Neu\Component\Console\Terminal;
use Psl\Iter;
use Psl\Regex;
use Psl\Str;

abstract class AbstractFormatter implements WrappingFormatterInterface
{
    /**
     * Default styles.
     *
     * @var array<string, array{foreground: ?ForegroundColor, background: ?BackgroundColor, effects: list<Effect>}>
     */
    protected static array $defaultStyles = [];

    /**
     * Whether this formatter is decorated or not.
     */
    protected bool $decorated = false;

    /**
     * @var array<string, StyleInterface>
     */
    protected array $styles = [];

    /**
     * @param array<string, StyleInterface> $styles
     */
    public function __construct(?bool $decorated = null, array $styles = [])
    {
        if (null === $decorated) {
            $decorated = Terminal::hasColorSupport();
        }

        $this->decorated = $decorated;

        foreach (static::$defaultStyles as $name => $style) {
            $style = new Style($style['background'], $style['foreground'], $style['effects']);
            $this->addStyle($name, $style);
        }

        foreach ($styles as $name => $style) {
            $this->addStyle($name, $style);
        }
    }

    /**
     * @inheritDoc
     */
    public function setDecorated(bool $decorated): self
    {
        $this->decorated = $decorated;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * @inheritDoc
     */
    public function addStyle(string $name, StyleInterface $style): self
    {
        $this->styles[Str\lowercase($name)] = $style;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasStyle(string $name): bool
    {
        return Iter\contains_key($this->styles, Str\lowercase($name));
    }

    /**
     * @inheritDoc
     */
    public function getStyle(string $name): StyleInterface
    {
        return $this->styles[Str\lowercase($name)];
    }

    /**
     * Escapes "<" special char in given text.
     */
    public static function escape(string $text): string
    {
        $text = Regex\replace($text, "/([^\\\\]?)</", '$1\\<');
        return self::escapeTrailingBackslash($text);
    }

    /**
     * Escapes trailing "\" in given text.
     */
    public static function escapeTrailingBackslash(string $text): string
    {
        if (Str\ends_with($text, '\\')) {
            $len = Str\length($text);
            $text = Str\trim_right($text, '\\');
            $text = Str\replace("\0", '', $text);
            $text .= Str\repeat("\0", $len - Str\length($text));
        }

        return $text;
    }
}
