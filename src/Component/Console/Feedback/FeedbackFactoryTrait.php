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

use Neu\Component\Console\Output\OutputInterface;

trait FeedbackFactoryTrait
{
    /**
     * Construct and return a new {@see ProgressBarFeedback} instance.
     *
     * @param int $total The total number of progress steps
     * @param string $message The message presented with the feedback
     * @param int $interval The interval (in milliseconds) between updates of the indicator.
     */
    public function createProgressBarFeedback(OutputInterface $output, int $total, string $message = '', int $interval = 100): ProgressBarFeedback
    {
        $progress = new ProgressBarFeedback($output, $total, $message, $interval);

        /** @psalm-suppress MissingThrowsDocblock */
        $progress->setCharacterSequence(['▓', '', '░']);

        return $progress;
    }

    /**
     * Construct and return a new {@see CycleFeedback} instance.
     *
     * @param int $total The total number of cycles of the process
     * @param string $message The message presented with the feedback
     * @param int $interval The interval (in milliseconds) between updates of the indicator.
     */
    public function createCycleFeedback(OutputInterface $output, int $total, string $message = '', int $interval = 100): CycleFeedback
    {
        return new CycleFeedback($output, $total, $message, $interval);
    }
}
