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

use Neu\Component\Console\Exception\InvalidArgumentException;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

final class StyleStack
{
    /**
     * @var list<StyleInterface> $styles
     */
    private array $styles = [];

    private StyleInterface $defaultStyle;

    public function __construct(null|StyleInterface $defaultStyle = null)
    {
        $this->defaultStyle = $defaultStyle ?? new Style();
    }

    public function reset(): void
    {
        $this->styles = [];
    }

    /**
     * Pushes a style in the stack.
     */
    public function push(StyleInterface $style): self
    {
        $this->styles[] = $style;

        return $this;
    }

    /**
     * Pops a style from the stack.
     *
     * @throws InvalidArgumentException When style tags incorrectly nested
     */
    public function pop(null|StyleInterface $style = null): StyleInterface
    {
        if ($style === null) {
            $lastStyle = $this->getCurrent();
            $this->styles = Vec\values(Dict\take(
                $this->styles,
                Iter\count($this->styles) - 1,
            ));

            return $lastStyle;
        }

        if (Iter\is_empty($this->styles)) {
            return $this->getDefaultStyle();
        }

        // we need to preserve the index order when reversing the stack
        $styles = [];
        foreach ($this->styles as $index => $stackedStyle) {
            $styles[] = [$index, $stackedStyle];
        }

        $styles = Vec\reverse($styles);
        foreach ($styles as [$index, $stackedStyle]) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = Vec\values(Dict\slice($this->styles, 0, $index));

                return $stackedStyle;
            }
        }

        throw new InvalidArgumentException(
            'Incorrectly nested style tag found.',
        );
    }

    public function getCurrent(): StyleInterface
    {
        if ([] === $this->styles) {
            return $this->defaultStyle;
        }

        return Iter\last($this->styles);
    }

    public function getDefaultStyle(): StyleInterface
    {
        return $this->defaultStyle;
    }

    public function setDefaultStyle(StyleInterface $style): self
    {
        $this->defaultStyle = $style;

        return $this;
    }
}
