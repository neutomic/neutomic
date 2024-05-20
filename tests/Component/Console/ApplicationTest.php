<?php

declare(strict_types=1);

namespace Neu\Tests\Component\Console;

use Neu\Component\Console\Application;
use Neu\Component\Console\Command\Configuration as CommandConfiguration;
use Neu\Component\Console\Command\Registry\Registry;
use Neu\Component\Console\Configuration;
use Neu\Component\Console\Input\HandleInput;
use Neu\Component\Console\Output\HandleOutput;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Examples\Console\Command\HelloWorldCommand;
use PHPUnit\Framework\TestCase;
use Psl\IO\MemoryHandle;

final class ApplicationTest extends TestCase
{
    public function testRun(): void
    {
        $registry = new Registry();

        $registry->register(
            new CommandConfiguration('hello-world', 'Hello World Command'),
            new HelloWorldCommand(),
        );

        $application = new Application(
            Configuration::create('hello-world', '1.0.0', 'Hello World Application'),
            $registry,
        );

        $outputHandle = new MemoryHandle();
        $input = new HandleInput(new MemoryHandle(), ['hello-world']);
        $output = new HandleOutput($outputHandle);

        $exitCode = $application->run($input, $output);

        static::assertSame(0, $exitCode);

        $outputHandle->seek(0);

        static::assertSame("Hello World!" . OutputInterface::END_OF_LINE, $outputHandle->read());
    }
}
