<?php

declare(strict_types=1);

namespace Neu\Component\Console;

use Neu\Component\Console\Command\AbstractCommand;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\Configuration as CommandConfiguration;
use Neu\Component\Console\Input\AbstractBag;
use Neu\Component\Console\Input\Bag\ArgumentBag;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Argument;
use Neu\Component\Console\Input\Definition\DefinitionInterface;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\Definition\Mode;
use Neu\Component\Console\Input\Definition\Option;
use Neu\Component\Console\Input\InputInterface;
use Neu\Component\Console\Output\OutputInterface;
use Psl\Dict;
use Psl\Iter;
use Psl\Math;
use Psl\Str;
use Psl\Vec;

use function array_keys;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function max;
use function mb_strlen;
use function str_repeat;
use function wordwrap;

/**
 * The `HelpScreen` class renders out a usage screen given the available `Flag`,
 * `Option`, and `Argument` objects available as well as available commands that
 * can be executed.
 */
final class HelpScreen
{
    /**
     * The optional `name` of the application when not outputting a {@see HelpScreen}
     * for a specific {@see CommandInterface}.
     */
    protected string $name = '';

    /**
     * The available commands configuration objects available.
     *
     * @var array<string, CommandConfiguration>
     */
    protected array $commands;

    /**
     * The current command configuration the {@see HelpScreen} refers to.
     */
    protected ?CommandConfiguration $configuration = null;

    /**
     * The available {@see Flag} objects accepted.
     */
    protected FlagBag $flags;

    /**
     * The available {@see Option} objects accepted.
     */
    protected OptionBag $options;

    /**
     * The available {@see Argument} objects accepted.
     */
    protected ArgumentBag $arguments;

    /**
     * Construct a new instance of the {@see HelpScreen}.
     */
    public function __construct(private readonly Application $application, InputInterface $input)
    {
        $this->commands = Dict\reindex(
            $application->getRegistry()->getConfigurations(),
            static fn(CommandConfiguration $configuration): string => $configuration->name,
        );

        $this->arguments = $input->getArguments();
        $this->flags = $input->getFlags();
        $this->options = $input->getOptions();
    }

    /**
     * Build and return the markup for the `HelpScreen`.
     */
    public function render(): string
    {
        $lines = [];
        $heading = $this->renderHeading();
        if ($heading !== '') {
            $lines[] = $heading;
        }

        $lines[] = $this->renderUsage();
        if (0 !== count($this->arguments->all())) {
            $output = $this->renderSection($this->arguments);
            if ($output) {
                $lines[] = '<fg=yellow>Arguments</>' . OutputInterface::END_OF_LINE . $output;
            }
        }

        if (0 !== count($this->flags->all())) {
            $output = $this->renderSection($this->flags);
            if ($output) {
                $lines[] = '<fg=yellow>Flags</>' . OutputInterface::END_OF_LINE . $output;
            }
        }

        if (0 !== count($this->options->all())) {
            $output = $this->renderSection($this->options);
            if ($output) {
                $lines[] = '<fg=yellow>Options</>' . OutputInterface::END_OF_LINE . $output;
            }
        }

        if (($this->configuration === null) && 0 !== count($this->commands)) {
            $lines[] = $this->renderCommands();
        }

        return implode(OutputInterface::END_OF_LINE . OutputInterface::END_OF_LINE, $lines) . OutputInterface::END_OF_LINE;
    }

    /**
     * Build and return the markup for the heading of the `HelpScreen`. This is
     * either the name of the application (when not rendering for a specific
     * `Command`) or the name and description of the `Command`.
     */
    protected function renderHeading(): string
    {
        $lines = [];
        if ($this->configuration !== null) {
            $command = $this->configuration;
            $description = $command->description;
            if ($description !== '') {
                $lines[] = $command->name . ' - ' . $description;
            } else {
                $lines[] = $command->name;
            }
        } else {
            $configuration = $this->application->getConfiguration();

            $banner = $configuration->banner;
            if ($banner !== '') {
                $lines[] = $banner;
            }

            $name = '<fg=green>' . $configuration->name . '</>';
            if ($configuration->version !== '') {
                $name .= ' version <fg=yellow>' . $configuration->version . '</>';
            }

            $lines[] = $name;
        }

        return implode(OutputInterface::END_OF_LINE, $lines);
    }

    /**
     * When rendering a for a `Command`, this method builds and returns the usage.
     */
    protected function renderUsage(): string
    {
        $usage = [];
        if ($this->configuration !== null) {
            $configuration = $this->configuration;

            $usage[] = $configuration->name;

            foreach ($configuration->flags as $flag) {
                $flg = $flag->getFormattedName($flag->getName());
                $alias = $flag->getAlias();
                if (!Str\is_empty($alias)) {
                    $flg .= '|' . $flag->getFormattedName($alias);
                }

                if ($flag->getMode() === Mode::Optional) {
                    $usage[] = '[' . $flg . ']';
                } else {
                    $usage[] = $flg;
                }
            }

            foreach ($configuration->options as $option) {
                $opt = $option->getFormattedName($option->getName());
                $alias = $option->getAlias();
                if (!Str\is_empty($alias)) {
                    $opt .= '|' . $option->getFormattedName($alias);
                }

                $opt .= '="..."';
                if ($option->getMode() === Mode::Optional) {
                    $usage[] = '[' . $opt . ']';
                } else {
                    $usage[] = $opt;
                }
            }

            foreach ($configuration->arguments as $argument) {
                $arg = $argument->getName();
                $alias = $argument->getAlias();
                if (!Str\is_empty($alias)) {
                    $arg .= '|' . $argument->getFormattedName($alias);
                }

                $arg = '<' . $arg . '>';
                if ($argument->getMode() === Mode::Optional) {
                    $usage[] = '[' . $arg . ']';
                } else {
                    $usage[] = $arg;
                }
            }
        } else {
            $usage[] = 'command';
            $usage[] = '[--flag|-f]';
            $usage[] = '[--option|-o="..."]';
            $usage[] = '[<argument>]';
        }

        return Str\format(
            '<fg=yellow>Usage</>%s  %s',
            OutputInterface::END_OF_LINE,
            Str\join($usage, ' '),
        );
    }

    /**
     * Build and return a specific section of available `Input` objects the user
     * may specify.
     *
     * @template T of DefinitionInterface
     *
     * @param AbstractBag<T> $arguments
     */
    protected function renderSection(AbstractBag $arguments): string
    {
        $entries = [];
        foreach ($arguments as $argument) {
            $name = $argument->getFormattedName($argument->getName());
            $alias = $argument->getAlias();
            if (!Str\is_empty($alias)) {
                $name = $argument->getFormattedName($alias) . ', ' . $name;
            }

            $entries[$name] = $argument->getDescription();
        }

        $maxLength = max(array_map(
            static fn(string $key): int => mb_strlen($key),
            array_keys($entries),
        ));

        $descriptionLength = Terminal::getWidth() - 6 - $maxLength;
        $output = [];
        foreach ($entries as $name => $description) {
            $formatted = '  ' . Str\pad_right($name, $maxLength);
            $formatted = Str\format('<fg=green>%s</>', $formatted);
            $description = Str\split(
                wordwrap($description, $descriptionLength, '{{NC-BREAK}}'),
                '{{NC-BREAK}}',
            );
            $formatted .= '  ' . array_shift($description);
            $description = array_values($description);
            $pad = str_repeat(' ', $maxLength + 6);
            foreach ($description as $desc) {
                $formatted .= OutputInterface::END_OF_LINE . $pad . $desc;
            }

            $output[] = '  ' . $formatted;
        }

        return Str\join($output, OutputInterface::END_OF_LINE);
    }

    /**
     * Build the list of available `Command` objects that can be called and their
     * descriptions.
     */
    protected function renderCommands(): string
    {
        $this->commands = Dict\sort_by_key($this->commands);

        $maxLength = Math\max(
            Vec\map(
                Vec\keys($this->commands),
                static function ($key): int {
                    $width = Str\width($key);
                    if (Str\contains($key, ':')) {
                        $width += 2;
                    }

                    return $width;
                },
            ),
        ) ?? 0;
        $descriptionLength = Terminal::getWidth() - 4 - $maxLength;

        $output = [];
        $namespaces = [];
        foreach ($this->commands as $name => $command) {
            if ($command->hidden) {
                continue;
            }

            if (Str\contains($name, ':')) {
                $prefix = '  ';
                $components = Str\split($name, ':');
                $namespace = Iter\first($components);
                if (!Iter\contains($namespaces, $namespace)) {
                    $output[] = $prefix . '<fg=yellow>' . $namespace . '</>';
                    $namespaces[] = $namespace;
                }

                $formatted = $prefix . '<' . ($command->enabled ? 'fg=green' : 'bg=red;fg=white') . '>' .
                    Str\pad_right($name, $maxLength - 2) .
                    '</>';
            } else {
                $formatted = '<' . ($command->enabled ? 'fg=green' : 'bg=red;fg=white') . '>' . Str\pad_right($name, $maxLength) . '</>';
            }

            $description = Str\split(
                wordwrap(
                    $command->description,
                    $descriptionLength,
                    '{{NC-BREAK}}',
                ),
                '{{NC-BREAK}}',
            );
            $formatted .= '  ' . array_shift($description);
            $description = array_values($description);

            $pad = str_repeat(' ', $maxLength + 4);
            foreach ($description as $desc) {
                $formatted .= OutputInterface::END_OF_LINE . $pad . $desc;
            }

            $output[] = '  ' . $formatted;
        }

        return '<fg=yellow>Available Commands:</>' . OutputInterface::END_OF_LINE . implode(OutputInterface::END_OF_LINE, $output);
    }

    /**
     * Set the `Argument` objects to render information for.
     */
    public function setArguments(ArgumentBag $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Set the command configuration to render a help screen for.
     */
    public function setCommandConfiguration(CommandConfiguration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Set the `Command` objects to render information for.
     *
     * @param array<string, AbstractCommand> $commands
     */
    public function setCommands(array $commands): self
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * Set the `Flag` objects to render information for.
     *
     * @param FlagBag $flags The `Flag` objects available
     */
    public function setFlags(FlagBag $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Set the `Input` the help screen should read all available parameters and
     * commands from.
     */
    public function setInput(InputInterface $input): self
    {
        $this->arguments = $input->getArguments();
        $this->flags = $input->getFlags();
        $this->options = $input->getOptions();

        return $this;
    }

    /**
     * Set the name of the application.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the `Option` objects to render information for.
     */
    public function setOptions(OptionBag $options): self
    {
        $this->options = $options;

        return $this;
    }
}
