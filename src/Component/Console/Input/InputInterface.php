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

namespace Neu\Component\Console\Input;

use Neu\Component\Console\Exception\InvalidInputDefinitionException;
use Neu\Component\Console\Exception\NonInteractiveInputException;
use Neu\Component\Console\Input\Bag\ArgumentBag;
use Neu\Component\Console\Input\Bag\FlagBag;
use Neu\Component\Console\Input\Bag\OptionBag;
use Neu\Component\Console\Input\Definition\Argument;
use Neu\Component\Console\Input\Definition\Flag;
use Neu\Component\Console\Input\Definition\Option;

/**
 * The {@see InputInterface} class contains all available {@see Flag}, {@see Argument}, and {@see Option}
 * objects available to parse given the provided input.
 */
interface InputInterface
{
    /**
     * Add a new {@see Argument} candidate to be parsed from input.
     */
    public function addArgument(Argument $argument): self;

    /**
     * Add a new {@see Flag} candidate to be parsed from input.
     */
    public function addFlag(Flag $flag): self;

    /**
     * Add a new {@see Option} candidate to be parsed from input.
     */
    public function addOption(Option $option): self;

    /**
     * Parse and retrieve the active command name from the raw input.
     *
     * @return null|non-empty-string The active command name or null if none.
     */
    public function getActiveCommand(): null|string;

    /**
     * Retrieve an {@see Argument} by its key or alias.
     *
     * @throws InvalidInputDefinitionException If the argument does not exist.
     */
    public function getArgument(string $key): Argument;

    /**
     * Retrieve all {@see Argument} candidates as an {@see ArgumentBag}.
     */
    public function getArguments(): ArgumentBag;

    /**
     * Retrieve a {@see Flag} by its key or alias.
     *
     * @throws InvalidInputDefinitionException If the flag does not exist.
     */
    public function getFlag(string $key): Flag;

    /**
     * Retrieve all {@see Flag} candidates as a {@see FlagBag}.
     */
    public function getFlags(): FlagBag;

    /**
     * Retrieve an {@see Option} by its key or alias.
     *
     * @throws InvalidInputDefinitionException If the option does not exist.
     */
    public function getOption(string $key): Option;

    /**
     * Retrieve all {@see Option} candidates as an {@see OptionBag}.
     */
    public function getOptions(): OptionBag;

    /**
     * Read in and return input from the user.
     *
     * @param positive-int|null $length The number of bytes to read from the input stream.
     *
     * @throws NonInteractiveInputException
     */
    public function getUserInput(null|int $length = null): string;

    /**
     * Parse input for all {@see Flag}, {@see Option}, and {@see Argument} candidates.
     */
    public function parse(bool $rewind = false): void;

    /**
     * Validate all {@see Flag}, {@see Option}, and {@see Argument} candidates.
     */
    public function validate(): void;

    /**
     * Set the arguments. This will override all existing arguments.
     */
    public function setArguments(ArgumentBag $arguments): self;

    /**
     * Set the flags. This will override all existing flags.
     */
    public function setFlags(FlagBag $flags): self;

    /**
     * Set the options. This will override all existing options.
     */
    public function setOptions(OptionBag $options): self;

    /**
     * Return whether the input is interactive.
     */
    public function isInteractive(): bool;

    /**
     * Sets the input interactivity.
     */
    public function setInteractive(bool $interactive): self;

    /**
     * Get the raw input stream.
     *
     * @return object|resource|null The raw input stream object or resource, or null if not available.
     */
    public function getStream(): mixed;
}
