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

/**
 * Formatter style interface for defining styles.
 */
interface StyleInterface
{
    /**
     * Sets style foreground color.
     */
    public function setForeground(null|ForegroundColor $color = null): self;

    /**
     * Sets style background color.
     */
    public function setBackground(null|BackgroundColor $color = null): self;

    /**
     * Sets some specific style effect.
     */
    public function setEffect(Effect $effect): self;

    public function setHref(string $url): self;

    /**
     * Applies the style to a given text.
     */
    public function apply(string $text): string;
}
