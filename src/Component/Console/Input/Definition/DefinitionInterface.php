<?php

declare(strict_types=1);

namespace Neu\Component\Console\Input\Definition;

use Neu\Component\Console\Exception\MissingValueException;

/**
 * An `DefinitionInterface` defines the name and type of input that may be accepted
 * by the user.
 *
 * @template T
 */
interface DefinitionInterface
{
    /**
     * Retrieve the name of the `DefinitionInterface`.
     */
    public function getName(): string;

    /**
     * Retrieve the alias of the `DefinitionInterface`.
     *
     * @return non-empty-string|null
     */
    public function getAlias(): null|string;

    /**
     * Retrieve the description of the `DefinitionInterface`.
     */
    public function getDescription(): string;

    /**
     * Retrieve the mode of the `DefinitionInterface`.
     */
    public function getMode(): Mode;

    /**
     * Returns if the `DefinitionInterface` has been assigned a value by the parser.
     */
    public function exists(): bool;

    /**
     * Retrieve the value of the `DefinitionInterface` as specified by the user.
     *
     * @throws MissingValueException If the definition has not been assigned a value.
     *
     * @return T
     */
    public function getValue(): mixed;

    /**
     * Retrieve the formatted name suitable for output in a help screen or
     * documentation.
     */
    public function getFormattedName(string $name): string;
}
