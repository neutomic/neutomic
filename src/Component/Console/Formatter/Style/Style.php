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

namespace Neu\Component\Console\Formatter\Style;

use Psl\Env;
use Psl\Iter;
use Psl\Str;

final class Style implements StyleInterface
{
    /**
     * @var null|array{open: ForegroundColor, close: string}
     */
    private null|array $foreground = null;
    /**
     * @var null|array{open: BackgroundColor, close: string}
     */
    private null|array $background = null;

    /**
     * @var list<array{open: Effect, close: string}>
     */
    private array $effects = [];
    private null|string $href = null;
    private null|bool $handlesHrefGracefully = null;

    /**
     * @param list<Effect> $effects
     */
    public function __construct(null|BackgroundColor $background = null, null|ForegroundColor $foreground = null, array $effects = [])
    {
        $this->setForeground($foreground);
        $this->setBackground($background);
        foreach ($effects as $effect) {
            $this->setEffect($effect);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setForeground(null|ForegroundColor $color = null): self
    {
        if ($color === null) {
            $this->foreground = null;
            return $this;
        }

        $this->foreground = ['open' => $color, 'close' => '39'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setBackground(null|BackgroundColor $color = null): self
    {
        if ($color === null) {
            $this->background = null;
            return $this;
        }

        $this->background = ['open' => $color, 'close' => '49'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setEffect(Effect $effect): self
    {
        $closing = match ($effect) {
            Effect::Bold => '22',
            Effect::Underline => '24',
            Effect::Blink => '25',
            Effect::Reverse => '27',
            Effect::Conceal => '28'
        };

        $this->effects[] = ['open' => $effect, 'close' => $closing];

        return $this;
    }

    /**
     * @ignore
     */
    #[\Override]
    public function setHref(string $url): self
    {
        $this->href = $url;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function apply(string $text): string
    {
        $openCodes = [];
        $closeCodes = [];
        if ($this->handlesHrefGracefully === null) {
            $this->handlesHrefGracefully = 'JetBrains-JediTerm' !== Env\get_var('TERMINAL_EMULATOR') && null === Env\get_var('KONSOLE_VERSION');
        }

        if ($this->foreground !== null) {
            ['open' => $open, 'close' => $close] = $this->foreground;
            $openCodes[] = $open->value;
            $closeCodes[] = $close;
        }

        if ($this->background !== null) {
            ['open' => $open, 'close' => $close] = $this->background;
            $openCodes[] = $open->value;
            $closeCodes[] = $close;
        }

        foreach ($this->effects as ['open' => $open, 'close' => $close]) {
            $openCodes[] = $open->value;
            $closeCodes[] = $close;
        }

        if ($this->href !== null && $this->handlesHrefGracefully) {
            $text = Str\format(
                "\033]8;;%s\033\\%s\033]8;;\033\\",
                $this->href,
                $text,
            );
        }

        if (Iter\is_empty($openCodes)) {
            return $text;
        }

        return Str\format(
            "\033[%sm%s\033[%sm",
            Str\join($openCodes, ';'),
            $text,
            Str\join($closeCodes, ';'),
        );
    }
}
