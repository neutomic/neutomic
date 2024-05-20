<?php

declare(strict_types=1);

namespace Neu\Component\Console\Formatter\Style;

/**
 * Formatter style interface for defining styles.
 */
interface StyleInterface
{
    /**
     * Sets style foreground color.
     */
    public function setForeground(?ForegroundColor $color = null): self;

    /**
     * Sets style background color.
     */
    public function setBackground(?BackgroundColor $color = null): self;

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
