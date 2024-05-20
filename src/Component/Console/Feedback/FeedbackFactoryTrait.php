<?php

declare(strict_types=1);

namespace Neu\Component\Console\Feedback;

use Neu\Component\Console\Output\OutputInterface;

trait FeedbackFactoryTrait
{
    /**
     * Construct and return a new instance of `ProgressBarFeedback`.
     *
     * @param int $total The total number of progress steps
     * @param string $message The message presented with the feedback
     * @param int $interval The interval (in milliseconds) between updates of the indicator.
     */
    public function createProgressBarFeedback(OutputInterface $output, int $total, string $message = '', int $interval = 100): ProgressBarFeedback
    {
        $progress = new ProgressBarFeedback($output, $total, $message, $interval);

        $progress->setCharacterSequence(['▓', '', '░']);

        return $progress;
    }

    /**
     * Construct and return a new `CycleFeedback` object.
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
