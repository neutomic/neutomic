<?php

declare(strict_types=1);

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
