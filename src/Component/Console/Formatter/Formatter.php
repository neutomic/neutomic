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

use Neu\Component\Console\Exception\InvalidCharacterSequenceException;
use Neu\Component\Console\Formatter\Style\BackgroundColor;
use Neu\Component\Console\Formatter\Style\Effect;
use Neu\Component\Console\Formatter\Style\ForegroundColor;
use Neu\Component\Console\Formatter\Style\Style;
use Neu\Component\Console\Formatter\Style\StyleInterface;
use Neu\Component\Console\Formatter\Style\StyleStack;
use Psl\Dict;
use Psl\Iter;
use Psl\Regex;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;

use function preg_match_all;

use const PREG_OFFSET_CAPTURE;

/**
 * @psalm-suppress MissingThrowsDocblock
 */
final class Formatter extends AbstractFormatter
{
    /**
     * @var array<string, array{foreground: ?ForegroundColor, background: ?BackgroundColor, effects: list<Effect>}>
     */
    protected static array $defaultStyles = [
        'comment' => [
            'foreground' => ForegroundColor::BrightYellow,
            'background' => null,
            'effects' => [],
        ],
        'success' => [
            'foreground' => ForegroundColor::BrightGreen,
            'background' => null,
            'effects' => [],
        ],
        'warning' => [
            'foreground' => ForegroundColor::Black,
            'background' => BackgroundColor::BrightYellow,
            'effects' => [],
        ],
        'info' => [
            'foreground' => ForegroundColor::BrightBlue,
            'background' => null,
            'effects' => [],
        ],
        'question' => [
            'foreground' => ForegroundColor::BrightCyan,
            'background' => null,
            'effects' => [],
        ],
        'error' => [
            'foreground' => ForegroundColor::White,
            'background' => BackgroundColor::BrightRed,
            'effects' => [],
        ],
    ];

    private StyleStack $styleStack;

    /**
     * @param array<string, StyleInterface> $styles
     */
    public function __construct(null|bool $decorated = null, array $styles = [])
    {
        parent::__construct($decorated, $styles);

        $this->styleStack = new StyleStack();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function format(string $message, int $width = 0): string
    {
        $offset = 0;
        $output = '';
        $currentLineLength = 0;
        preg_match_all(
            '#<(([a-z][^<>]*+) | /([a-z][^<>]*+)?)>#ix',
            $message,
            $matches,
            PREG_OFFSET_CAPTURE,
        );

        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];
            if (0 !== $pos && '\\' === $message[$pos - 1]) {
                continue;
            }

            /** @var int<0, max> $length */
            $length = $pos - $offset;
            // add the text up to the next tag
            [$decorated_text, $currentLineLength] = $this->applyCurrentStyle(
                Byte\slice($message, $offset, $length),
                $output,
                $width,
                $currentLineLength,
            );

            $output .= $decorated_text;
            $offset = $pos + Byte\length($text);
            // opening tag?
            $open = '/' !== $text[1];
            if ($open) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = $matches[3][$i][0] ?? '';
            }

            if (!$open && !$tag) {
                // </>
                $this->styleStack->pop();
            } else {
                $style = $this->createStyleFromString($tag);
                if ($style === null) {
                    [$decorated_text, $currentLineLength] = $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
                    $output .= $decorated_text;
                } elseif ($open) {
                    $this->styleStack->push($style);
                } else {
                    $this->styleStack->pop($style);
                }
            }
        }

        [$decorated_text] = $this->applyCurrentStyle(Byte\slice($message, $offset), $output, $width, $currentLineLength);
        $output .= $decorated_text;

        if (Byte\contains($output, "\0")) {
            $output = Byte\replace($output, "\0", '\\');
        }

        return Byte\replace($output, '\\<', '<');
    }

    /**
     * Applies current style from stack to text, if it must be applied.
     *
     * @return array{0: string, 1: int}
     */
    private function applyCurrentStyle(string $text, string $current, int $width, int $currentLineLength): array
    {
        if ('' === $text) {
            return ['', $currentLineLength];
        }

        if (0 <= $width) {
            return [
                $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text,
                $currentLineLength,
            ];
        }

        if (0 === $currentLineLength && '' !== $current) {
            $text = Byte\trim_left($text);
        }

        if ($currentLineLength > 0 && $width > $currentLineLength) {
            /** @var int<0, max> $i */
            $i = $width - $currentLineLength;
            $prefix = Byte\slice($text, 0, $i) . "\n";
            $text = Byte\slice($text, $i);
        } else {
            $prefix = '';
        }

        $matches = Regex\first_match($text, "~(\\n)$~");
        $text = $prefix . Regex\replace($text, '~([^\\n]{' . ((string) $width) . '})\\ *~', "\$1\n");
        $text = Byte\trim_right($text, "\n") . ($matches[1] ?? '');
        if (!$currentLineLength && '' !== $current && !Byte\ends_with($current, "\n")) {
            $text = "\n" . $text;
        }

        $lines = Byte\split($text, "\n");
        foreach ($lines as $line) {
            $currentLineLength += Byte\length($line);
            if ($width <= $currentLineLength) {
                $currentLineLength = 0;
            }
        }

        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }

        return [Str\join($lines, "\n"), $currentLineLength];
    }

    /**
     * Tries to create new style instance from string.
     */
    private function createStyleFromString(string $string): null|StyleInterface
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if ('' === $string) {
            return null;
        }

        $attributes = Vec\map(
            Regex\split($string, '~(?<!\\\\)[; ]~'),
            Byte\trim(...),
        );

        if ([] === $attributes) {
            return null;
        }

        $style = new Style();
        $valid = false;

        $backgrounds = Dict\reindex(BackgroundColor::cases(), static fn (BackgroundColor $enum) => $enum->name);
        $foregrounds = Dict\reindex(ForegroundColor::cases(), static fn (ForegroundColor $enum) => $enum->name);
        $effects = Dict\reindex(Effect::cases(), static fn (Effect $enum) => $enum->name);

        $parse_attribute_value = static function (string $attribute, bool $normalize = true): string {
            if (Byte\contains($attribute, '=')) {
                [, $value] = Byte\split($attribute, '=', 2);
            } else {
                $value = $attribute;
            }

            if (!$normalize) {
                return $value;
            }

            $value = Byte\replace_every($value, [
                '"' => '',
                '\'' => '',
                '-' => ' ',
            ]);

            return Str\join(Vec\map(Byte\split($value, ' '), Byte\capitalize(...)), '');
        };

        foreach ($attributes as $attribute) {
            if (Byte\starts_with($attribute, 'bg=') || Byte\starts_with($attribute, 'background=')) {
                $background = $parse_attribute_value($attribute);
                if ('' === $background) {
                    continue;
                }

                if ('Random' === $background) {
                    $background = Iter\random($backgrounds);
                } elseif (!isset($backgrounds[$background])) {
                    throw new InvalidCharacterSequenceException(
                        Str\format('Background "%s" does not exists.', $background),
                    );
                }

                $valid = true;
                $style->setBackground($backgrounds[$background]);
                continue;
            }

            if (Byte\starts_with($attribute, 'fg=') || Byte\starts_with($attribute, 'foreground=') || Byte\starts_with($attribute, 'color=')) {
                $foreground = $parse_attribute_value($attribute);
                if ('' === $foreground) {
                    continue;
                }

                if ('Random' === $foreground) {
                    $foreground = Iter\random($foregrounds);
                } elseif (!isset($foregrounds[$foreground])) {
                    throw new InvalidCharacterSequenceException(
                        Str\format('Foreground "%s" does not exists.', $foreground),
                    );
                }

                $valid = true;
                $style->setForeground($foregrounds[$foreground]);
                continue;
            }

            if (Byte\starts_with($attribute, 'href=')) {
                $href = $parse_attribute_value($attribute, normalize: false);
                if ('' === $href) {
                    continue;
                }

                $valid = true;
                $style->setHref($href);

                continue;
            }

            $effect = $parse_attribute_value($attribute);
            if (!isset($effects[$effect])) {
                continue;
            }

            $valid = true;
            $style->setEffect($effects[$effect]);
        }

        return $valid ? $style : null;
    }
}
