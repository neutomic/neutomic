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

/**
 * Defines the interface for a command registry.
 *
 * This interface provides methods for registering commands and configurations,
 * checking their existence, and retrieving them.
 *
 * It also allows incorporating another registry's entries into this one.
 */
interface RegistryInterface
{
    /**
     * Registers a configuration and its associated command.
     *
     * @param Configuration $configuration The configuration to register.
     * @param CommandInterface $command The command associated with the configuration.
     */
    public function register(Configuration $configuration, CommandInterface $command): void;

    /**
     * Incorporates the entries from another registry into this one.
     *
     * This method integrates all configurations and commands from the specified registry
     * into the current registry.
     *
     * The source registry remains unmodified.
     *
     * @param RegistryInterface $registry The registry whose entries are to be incorporated.
     */
    public function incorporate(RegistryInterface $registry): void;

    /**
     * Checks if a configuration or command with the specified name exists.
     *
     * @param non-empty-string $name The name of the configuration or command to check.
     *
     * @return bool Returns true if the configuration or command exists, otherwise false.
     */
    public function has(string $name): bool;

    /**
     * Retrieves a configuration by its name.
     *
     * @param non-empty-string $name The name of the configuration to retrieve.
     *
     * @throws CommandNotFoundException If no configuration with the given name exists.
     *
     * @return Configuration Returns the requested configuration.
     */
    public function getConfiguration(string $name): Configuration;

    /**
     * Retrieves a command by its name.
     *
     * @param non-empty-string $name The name of the command to retrieve.
     *
     * @throws CommandNotFoundException If no command with the given name exists.
     *
     * @return CommandInterface Returns the requested command.
     */
    public function getCommand(string $name): CommandInterface;

    /**
     * Retrieves all configurations registered in the registry.
     *
     * @return list<Configuration> A list of all registered configurations.
     */
    public function getConfigurations(): array;
}
