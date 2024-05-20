<?php

declare(strict_types=1);

namespace Neu\Component\Console\Formatter;

use Neu\Component\Console\Exception\InvalidCharacterSequenceException;
use Neu\Component\Console\Formatter\Style\BackgroundColor;
use Neu\Component\Console\Formatter\Style\Effect;
use Neu\Component\Console\Formatter\Style\ForegroundColor;
use Neu\Component\Console\Formatter\Style\Style;
use Neu\Component\Console\Formatter\Style\StyleInterface;
use Neu\Component\Console\Formatter\Style\StyleStack;
use Psl\Dict;
use Psl\Regex;
use Psl\Str;
use Psl\Str\Byte;

use function array_map;
use function array_rand;
use function explode;
use function implode;
use function ltrim;
use function preg_match_all;
use function rtrim;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use function ucfirst;

use const PREG_OFFSET_CAPTURE;

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
            $pos = (int)$match[1];
            $text = $match[0];
            if (0 !== $pos && '\\' === $message[$pos - 1]) {
                continue;
            }

            // add the text up to the next tag
            [$decorated_text, $currentLineLength] = $this->applyCurrentStyle(Byte\slice($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
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
            $text = ltrim($text);
        }

        if ($currentLineLength > 0 && $width > $currentLineLength) {
            $i = $width - $currentLineLength;
            $prefix = substr($text, 0, $i) . "\n";
            $text = substr($text, $i);
        } else {
            $prefix = '';
        }

        $matches = Regex\first_match($text, "~(\\n)$~");
        $text = $prefix . Regex\replace($text, '~([^\\n]{' . $width . '})\\ *~', "\$1\n");
        $text = rtrim($text, "\n") . ($matches[1] ?? '');
        if (!$currentLineLength && '' !== $current && !str_ends_with($current, "\n")) {
            $text = "\n" . $text;
        }

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $currentLineLength += strlen($line);
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
    private function createStyleFromString(string $string): ?StyleInterface
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if ('' === $string) {
            return null;
        }

        $attributes = array_map(
            trim(...),
            Regex\split($string, '~(?<!\\\\)[; ]~'),
        );

        if ([] === $attributes) {
            return null;
        }

        $style = new Style();
        $valid = false;

        $backgrounds = Dict\reindex(BackgroundColor::cases(), static fn(BackgroundColor $enum) => $enum->name);
        $foregrounds = Dict\reindex(ForegroundColor::cases(), static fn(ForegroundColor $enum) => $enum->name);
        $effects = Dict\reindex(Effect::cases(), static fn(Effect $enum) => $enum->name);

        $parse_attribute_value = static function (string $attribute, bool $normalize = true): string {
            if (str_contains($attribute, '=')) {
                [, $value] = explode('=', $attribute, 2);
            } else {
                $value = $attribute;
            }

            if (!$normalize) {
                return $value;
            }

            $value = str_replace(['"', '\'', '-',], ['', '', ' '], $value);

            return implode('', array_map(ucfirst(...), explode(' ', $value)));
        };

        foreach ($attributes as $attribute) {
            if (str_starts_with($attribute, 'bg=') || str_starts_with($attribute, 'background=')) {
                $background = $parse_attribute_value($attribute);
                if ('' === $background) {
                    continue;
                }

                if ('Random' === $background) {
                    $background = array_rand($backgrounds);
                } elseif (!isset($backgrounds[$background])) {
                    throw new InvalidCharacterSequenceException(
                        Str\format('Background "%s" does not exists.', $background),
                    );
                }

                $valid = true;
                $style->setBackground($backgrounds[$background]);
                continue;
            }

            if (str_starts_with($attribute, 'fg=') || str_starts_with($attribute, 'foreground=') || str_starts_with($attribute, 'color=')) {
                $foreground = $parse_attribute_value($attribute);
                if ('' === $foreground) {
                    continue;
                }

                if ('Random' === $foreground) {
                    $foreground = array_rand($foregrounds);
                } elseif (!isset($foregrounds[$foreground])) {
                    throw new InvalidCharacterSequenceException(
                        Str\format('Foreground "%s" does not exists.', $foreground),
                    );
                }

                $valid = true;
                $style->setForeground($foregrounds[$foreground]);
                continue;
            }

            if (str_starts_with($attribute, 'href=')) {
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
