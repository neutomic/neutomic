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

namespace Neu\Component\Console\Command\Registry;

use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\Configuration;
use Neu\Component\Console\Exception\CommandNotFoundException;
use Neu\Component\Console\Output\OutputInterface;
use Neu\Component\Utility\AlternativeFinder;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

final class Registry implements RegistryInterface
{
    /**
     * @var array<non-empty-string, Configuration>
     */
    private array $configurations = [];

    /**
     * @var array<non-empty-string, CommandInterface>
     */
    private array $commands = [];

    /**
     * @inheritDoc
     */
    public function register(Configuration $configuration, CommandInterface $command): void
    {
        $this->configurations[$configuration->name] = $configuration;
        $this->commands[$configuration->name] = $command;

        foreach ($configuration->aliases as $alias) {
            $this->configurations[$alias] = $configuration;
            $this->commands[$alias] = $command;
        }
    }

    /**
     * @inheritDoc
     */
    public function incorporate(RegistryInterface $registry): void
    {
        foreach ($registry->getConfigurations() as $configuration) {
            $this->register($configuration, $registry->getCommand($configuration->name));
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return Iter\contains_key($this->configurations, $name);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(string $name): Configuration
    {
        if (Iter\contains_key($this->configurations, $name)) {
            return $this->configurations[$name];
        }

        throw $this->buildException($name);
    }

    /**
     * @inheritDoc
     */
    public function getCommand(string $name): CommandInterface
    {
        if (Iter\contains_key($this->commands, $name)) {
            return $this->commands[$name];
        }

        throw $this->buildException($name);
    }

    /**
     * @inheritDoc
     */
    public function getConfigurations(): array
    {
        $configurations = [];
        foreach ($this->configurations as $configuration) {
            // Skip duplicate configurations (e.g. when a command configuration has multiple names/aliases)
            if (Iter\contains($configurations, $configuration)) {
                continue;
            }

            $configurations[] = $configuration;
        }

        return $configurations;
    }

    /**
     * @param non-empty-string $name
     *
     * @return CommandNotFoundException
     *
     * @psalm-suppress PossiblyInvalidPropertyFetch
     */
    private function buildException(string $name): CommandNotFoundException
    {
        $configurations = [];
        foreach ($this->getConfigurations() as $configuration) {
            $configurations[$configuration->name] = $configuration;
            foreach ($configuration->aliases as $alias) {
                $configurations[$alias] = $configurations;
            }
        }

        $allNames = Vec\keys($configurations);
        $message = Str\format('Command "%s" is not defined.', $name);
        $alternatives = AlternativeFinder::findAlternatives($name, $allNames);
        // remove hidden commands
        $alternatives = Vec\filter(
            $alternatives,
            static fn (string $name): bool => !$configurations[$name]->hidden,
        );
        if (!Iter\is_empty($alternatives)) {
            if (1 === Iter\count($alternatives)) {
                $message .= Str\format(
                    '%s%sDid you mean this?%s%s',
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                );
            } else {
                $message .= Str\format(
                    '%s%sDid you mean one of these?%s%s',
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                    OutputInterface::END_OF_LINE,
                );
            }

            foreach ($alternatives as $alternative) {
                $message .= Str\format('    - %s%s', $alternative, OutputInterface::END_OF_LINE);
            }
        }

        return new CommandNotFoundException($message);
    }
}
