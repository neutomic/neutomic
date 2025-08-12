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
use Psl\Str\Byte;
use Override;

/**
 * @psalm-suppress MissingThrowsDocblock
 */
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
    public function __construct(null|bool $decorated = null, array $styles = [])
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
    #[Override]
    public function setDecorated(bool $decorated): self
    {
        $this->decorated = $decorated;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addStyle(string $name, StyleInterface $style): self
    {
        $this->styles[Str\lowercase($name)] = $style;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasStyle(string $name): bool
    {
        return Iter\contains_key($this->styles, Str\lowercase($name));
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
        if (Byte\ends_with($text, '\\')) {
            $len = Byte\length($text);
            $text = Byte\trim_right($text, '\\');
            $text = Byte\replace($text, "\0", '');
            /** @var int<0, max> $remaining */
            $remaining = $len - Byte\length($text);
            $text .= Str\repeat("\0", $remaining);
        }

        return $text;
    }
}
