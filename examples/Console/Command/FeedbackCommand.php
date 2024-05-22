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

namespace Neu\Examples\Console\Command;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Feedback\FeedbackFactoryTrait;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Psl\Async;

#[Command('feedback', 'Example of using feedbacks')]
final readonly class FeedbackCommand implements CommandInterface
{
    use FeedbackFactoryTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        // Progress bar feedback
        $progressBar = $this->createProgressBarFeedback($output, 100, 'Downloading binaries...');
        $progressBar->setCharacterSequence(['▓', '▓', '░']);
        for ($i = 0; $i < 10; $i++) {
            $progressBar->advance();
            Async\sleep(0.05);
        }

        for ($i = 0; $i < 10; $i++) {
            $progressBar->advance(-1);
            Async\sleep(0.5);
        }

        for ($i = 0; $i < 99; $i++) {
            $progressBar->advance();
            Async\sleep(0.005);
        }

        $progressBar->finish();
        $output->writeLine('Download complete.');

        // Cycle feedback
        $cycle = $this->createCycleFeedback($output, 100, 'Waiting for client connection:', 10);
        $cycle->setCharacterSequence(['.', '..', '...', '....', '...', '..']);
        $cycle->setFinishCharacter("CONNECTED");

        for ($i = 0; $i < 99; $i++) {
            $cycle->advance();
            Async\sleep(0.05);
        }

        $cycle->finish();
        $output->writeLine('Done.');

        return 0;
    }
}
