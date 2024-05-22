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

namespace Neu\Component\Console\Feedback;

/**
 * A `FeedbackInterface` class handles the displaying of progress information to the user
 * for a specific task.
 */
interface FeedbackInterface
{
    /**
     * Progress the feedback display.
     */
    public function advance(int $increment = 1): void;

    /**
     * Force the feedback to end its output.
     */
    public function finish(): void;

    /**
     * Set the frequency the feedback should update.
     */
    public function setInterval(int $interval): self;

    /**
     * Set the message presented to the user to signify what the feedback
     * is referring to.
     */
    public function setMessage(string $message): self;

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
     * A template string used to construct additional information displayed before
     * the feedback indicator. The supported variables include message, percent,
     * elapsed, and estimated. These variables are denoted in the template '{:}'
     * notation. (i.e., '{:message} {:percent}').
     */
    public function setPrefix(string $prefix): self;

    /**
     * A template string used to construct additional information displayed after
     * the feedback indicator. The supported variables include message, percent,
     * elapsed, and estimated. These variables are denoted in the template '{:}'
     * notation. (i.e., '{:message} {:percent}').
     */
    public function setSuffix(string $suffix): self;

    /**
     * Set the total number of cycles (`advance` calls) the feedback should be
     * expected to take.
     */
    public function setTotal(int $total): self;
}
