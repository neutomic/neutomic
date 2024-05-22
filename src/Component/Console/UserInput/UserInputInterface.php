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

namespace Neu\Component\Console\UserInput;

use Neu\Component\Console\Exception\NonInteractiveInputException;

/**
 * User input handles presenting a prompt to the user and.
 *
 * @template T
 */
interface UserInputInterface
{
    /**
     * Set the display position (column, row).
     *
     * Implementation should not change position unless this method
     * is called.
     *
     * When changing positions, the implementation should always save the cursor
     * position, then restore it.
     *
     * @param null|array{0: int<0, max>, 1: int<0, max>} $position
     */
    public function setPosition(null|array $position): void;

    /**
     * Present the user with a prompt and return the inputted value.
     *
     * @throws NonInteractiveInputException
     *
     * @return T
     */
    public function prompt(string $message): mixed;
}
