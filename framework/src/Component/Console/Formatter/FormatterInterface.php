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

use Neu\Component\Console\Formatter\Style\StyleInterface;

/**
 * Formatter interface for console output.
 */
interface FormatterInterface
{
    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated(): bool;

    /**
     * Set the decorated flag.
     */
    public function setDecorated(bool $decorated): self;

    /**
     * Adds a new style.
     */
    public function addStyle(string $name, StyleInterface $style): self;

    /**
     * Checks if output formatter has style with specified name.
     */
    public function hasStyle(string $name): bool;

    /**
     * Gets style options from style with specified name.
     */
    public function getStyle(string $name): StyleInterface;

    /**
     * Formats a message according to the given styles.
     */
    public function format(string $message): string;
}
