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

namespace Neu\Component\Console;

use Neu\Component\Console\Command\ApplicationAwareCommandInterface;
use Neu\Component\Console\Command\ExitCode;
use Neu\Component\Console\Command\Registry\RegistryInterface;
use Neu\Component\Console\Event\AfterExecuteEvent;
use Neu\Component\Console\Event\BeforeExecuteEvent;
use Neu\Component\Console\Event\ThrowableEvent;
use Neu\Component\Console\Exception\CommandDisabledException;
use Neu\Component\Console\Input\Bag\ArgumentBag;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Console\Output\Verbosity;
use Neu\Component\Console\Recovery\Recovery;
use Neu\Component\Console\Recovery\RecoveryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Psl\Async;
use Psl\Env;
use Throwable;

/**
 * The application class is the main entry point for the console application.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class Application implements ApplicationInterface
{
    /**
     * The application configuration.
     */
    private Configuration $configuration;

    /**
     * The command registry instance to use to store and retrieve commands.
     */
    private RegistryInterface $registry;

    /**
     * The error handler used to handle exceptions thrown during command execution.
     */
    private RecoveryInterface $errorHandler;

    /**
     * The event dispatcher to dispatch events during the application lifecycle.
     */
    private null|EventDispatcherInterface $dispatcher;

    /**
     * The keyed semaphore is used to ensure that multiple commands can run concurrently, but not the same command.
     *
     * @var Async\KeyedSequence<string, array{Command\Configuration, Command\CommandInterface, InputInterface, OutputInterface}, int>
     */
    private Async\KeyedSequence $semaphore;

    public function __construct(Configuration $configuration, RegistryInterface $registry, null|RecoveryInterface $errorHandler = null, null|EventDispatcherInterface $dispatcher = null)
    {
        $this->configuration = $configuration;
        $this->registry = $registry;
        $this->errorHandler = $errorHandler ?? new Recovery();
        $this->dispatcher = $dispatcher;

        $this->semaphore = new Async\KeyedSequence(
            /**
             * @param string $command
             * @param array{Command\Configuration, Command\CommandInterface, InputInterface, OutputInterface} $input
             */
            function (string $command, array $input): int {
                [$configuration, $command, $input, $output] = $input;

                return $this->runContext($configuration, $command, $input, $output);
            },
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getRecovery(): RecoveryInterface
    {
        return $this->errorHandler;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getEventDispatcher(): null|EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function run(null|InputInterface $input = null, null|OutputInterface $output = null): int
    {
        Env\set_var('COLUMNS', (string) Terminal::getWidth());
        Env\set_var('LINES', (string) Terminal::getHeight());

        $input ??= Terminal::getInput();
        $output ??= Terminal::getOutput();

        try {
            $this->bootstrap($input);

            $input->parse();

            $name = $input->getActiveCommand();
            if ($this->configuration->ansiFlagEnabled && $input->getFlag('ansi')->exists()) {
                Terminal::setColorSupport(true);
                $output->getFormatter()->setDecorated(true);
            } elseif ($this->configuration->noAnsiFlagEnabled && $input->getFlag('no-ansi')->exists()) {
                Terminal::setColorSupport(false);
                $output->getFormatter()->setDecorated(false);
            }

            $verbosity_set = false;
            if ($this->configuration->quietFlagEnabled && $input->getFlag('quiet')->exists()) {
                $verbosity_set = true;
                Env\set_var('SHELL_VERBOSITY', (string)Verbosity::Quite->value);

                $output->setVerbosity(Verbosity::Quite);
                $input->setInteractive(false);
            } elseif ($this->configuration->noInteractionFlagEnabled && $input->getFlag('no-interaction')->exists()) {
                Terminal::setInteractive(false);
                $input->setInteractive(false);
            }

            if ($this->configuration->verboseFlagEnabled && !$verbosity_set) {
                $verbose_flag = $input->getFlag('verbose');
                $verbosity = $verbose_flag->exists() ? $verbose_flag->getValue() : 0;
                $verbosity = match ($verbosity) {
                    0 => Verbosity::Normal,
                    1 => Verbosity::Verbose,
                    2 => Verbosity::VeryVerbose,
                    default => Verbosity::Debug,
                };

                Env\set_var('SHELL_VERBOSITY', (string)$verbosity->value);
                $output->setVerbosity($verbosity);
            }

            if ($name === null) {
                if ($this->configuration->versionFlagEnabled && $input->getFlag('version')->exists()) {
                    $this->renderVersionInformation($output);
                } else {
                    $this->renderHelpScreen($input, $output);
                }

                $exitCode = ExitCode::Success;
            } else {
                $configuration = $this->registry->getConfiguration($name);
                $command = $this->registry->getCommand($name);

                $exitCode = $this->semaphore->waitFor($configuration->name, [
                    $configuration,
                    $command,
                    $input,
                    $output,
                ]);
            }
        } catch (Throwable $throwable) {
            $exitCode = null;
            if ($this->dispatcher !== null) {
                $dispatcher = $this->dispatcher;
                $event = $dispatcher->dispatch(new ThrowableEvent($input, $output, $throwable));
                $exitCode = $event->exitCode;
                $throwable = $event->throwable;
            }

            if ($exitCode === null || ExitCode::Success !== $exitCode) {
                $exitCode = $this->errorHandler->recover($input, $output, $throwable);
            }
        }

        return $this->terminate($input, $output, $exitCode instanceof ExitCode ? $exitCode->value : $exitCode);
    }

    /**
     * Run the command in the context of the application.
     *
     * @throws CommandDisabledException If the command is disabled.
     */
    private function runContext(
        Command\Configuration $configuration,
        Command\CommandInterface $command,
        Input\InputInterface $input,
        Output\OutputInterface $output,
    ): int {
        if (!$configuration->enabled) {
            throw new CommandDisabledException('Command "' . $configuration->name . '" is disabled.');
        }

        if ($command instanceof ApplicationAwareCommandInterface) {
            $command->setApplication($this);
        }

        $arguments = new ArgumentBag();
        $arguments->add($configuration->arguments->all());
        foreach ($input->getArguments()->getIterator() as $name => $argument) {
            $arguments->set($name, $argument);
        }

        $input->setArguments($arguments);

        $flags = new FlagBag();
        $flags->add($configuration->flags->all());
        foreach ($input->getFlags()->getIterator() as $name => $flag) {
            $flags->set($name, $flag);
        }
        $input->setFlags($flags);

        $options = new OptionBag();
        $options->add($configuration->options->all());
        foreach ($input->getOptions()->getIterator() as $name => $option) {
            $options->set($name, $option);
        }
        $input->setOptions($options);

        $input->parse(true);
        if ($this->configuration->helpFlagEnabled && $input->getFlag('help')->exists()) {
            $this->renderHelpScreen($input, $output, $configuration);
            return 0;
        }

        if ($this->configuration->versionFlagEnabled && $input->getFlag('version')->exists()) {
            $this->renderVersionInformation($output);
            return 0;
        }

        $input->validate();

        $dispatcher = $this->dispatcher;
        if ($dispatcher === null) {
            $exitCode = $command->run($input, $output);
            if ($exitCode instanceof ExitCode) {
                $exitCode = $exitCode->value;
            }

            return $exitCode;
        }

        $event = $dispatcher->dispatch(new BeforeExecuteEvent($input, $output));

        if ($event->commandShouldRun) {
            $exitCode = $command->run($input, $output);
            if ($exitCode instanceof ExitCode) {
                $exitCode = $exitCode->value;
            }
        } else {
            $exitCode = ExitCode::SkippedCommand->value;
        }

        return $exitCode;
    }

    /**
     * Bootstrap the application with default parameters and global settings.
     */
    private function bootstrap(InputInterface $input): void
    {
        /*
         * Add global flags
         */
        if ($this->configuration->helpFlagEnabled) {
            $input->addFlag(
                (new Flag('help', description: 'Display this help screen.'))->alias('h'),
            );
        }

        if ($this->configuration->versionFlagEnabled) {
            $input->addFlag(
                (new Flag('version', description: 'Display the application\'s version'))->alias('V'),
            );
        }

        if ($this->configuration->quietFlagEnabled) {
            $input->addFlag(
                (new Flag('quiet', description: 'Suppress all output.'))->alias('q'),
            );
        }

        if ($this->configuration->verboseFlagEnabled) {
            $input->addFlag(
                (new Flag('verbose', description: 'Set the verbosity of the application\'s output.'))->alias('v')->setStackable(true),
            );
        }

        if ($this->configuration->noInteractionFlagEnabled) {
            $input->addFlag(
                (new Flag('no-interaction', description: 'Force disable input interaction'))->alias('n'),
            );
        }

        if ($this->configuration->ansiFlagEnabled) {
            $input->addFlag(
                (new Flag('ansi', description: 'Force enable ANSI output')),
            );
        }

        if ($this->configuration->noAnsiFlagEnabled) {
            $input->addFlag(
                (new Flag('no-ansi', description: 'Force disable ANSI output')),
            );
        }
    }

    /**
     * Render the help screen for the application or the specified command.
     */
    private function renderHelpScreen(InputInterface $input, OutputInterface $output, null|Command\Configuration $configuration = null): void
    {
        $helpScreen = new HelpScreen($this, $input);
        if ($configuration !== null) {
            $helpScreen->setCommandConfiguration($configuration);
        }

        $output->write(
            $helpScreen->render()
        );
    }

    /**
     * Output version information of the current application.
     */
    private function renderVersionInformation(OutputInterface $output): void
    {
        $output->write('<fg=green>' . $this->configuration->name . '</>');
        if ('' !== $this->configuration->version) {
            $output->writeLine('version <fg=yellow>' . $this->configuration->version . '</>');
        }
    }

    /**
     * Termination method executed at the end of the application's run.
     */
    private function terminate(InputInterface $input, OutputInterface $output, int $exitCode): int
    {
        if ($this->dispatcher !== null) {
            $dispatcher = $this->dispatcher;
            $event = $dispatcher->dispatch(
                new AfterExecuteEvent($input, $output, $exitCode),
            );

            $exitCode = $event->exitCode;
        }

        if ($exitCode > ExitCode::ExitStatusOutOfRange->value) {
            $exitCode %= ExitCode::ExitStatusOutOfRange->value;
        }

        return $exitCode;
    }
}
