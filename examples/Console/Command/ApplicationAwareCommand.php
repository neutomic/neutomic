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
use Neu\Component\Console\Command\ApplicationAwareCommandInterface;
use Neu\Component\Console\Command\ApplicationAwareCommandTrait;
use Neu\Component\Console\Input;
use Neu\Component\Console\Output;
use Psl\Async;
use Psl\IO\MemoryHandle;

#[Command('appaware', 'A command that executes multiple sub-commands concurrently.')]
final readonly class ApplicationAwareCommand implements ApplicationAwareCommandInterface
{
    use ApplicationAwareCommandTrait;

    public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $time = time();

        $output->writeLine('I am going to execute multiple commands concurrently.');

        Async\concurrently([
            function () {
                $input = new Input\HandleInput(new MemoryHandle(), ['sleepy']);
                $output = new Output\HandleOutput(new MemoryHandle());

                $this->application->run($input, $output);
            },
            function () {
                $input = new Input\HandleInput(new MemoryHandle(), ['napping']);
                $output = new Output\HandleOutput(new MemoryHandle());

                $this->application->run($input, $output);
            },
            function () {
                $input = new Input\HandleInput(new MemoryHandle(), ['sleepy']);
                $output = new Output\HandleOutput(new MemoryHandle());

                $this->application->run($input, $output);
            },
        ]);

        $elapsed = time() - $time;

        $output->writeLine('I am done! it took me ' . $elapsed . ' seconds, but the sub-commands slept for total of 9 seconds.');

        return 0;
    }
}
