<?php

declare(strict_types=1);

namespace Neu\Component\Console\UserInput;

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
     * @param array{0: int, 1: int}
     */
    public function setPosition(?array $position): void;

    /**
     * Present the user with a prompt and return the inputted value.
     *
     * @return T
     */
    public function prompt(string $message): mixed;
}
