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

namespace Neu\Component\Console\Input\Definition;

use Neu\Component\Console\Exception\MissingValueException;

/**
 * An {@see DefinitionInterface} defines the name and type of input that may be accepted
 * by the user.
 *
 * @template T
 */
interface DefinitionInterface
{
    /**
     * Retrieve the name of the {@see DefinitionInterface}.
     */
    public function getName(): string;

    /**
     * Retrieve the alias of the {@see DefinitionInterface}.
     *
     * @return non-empty-string|null
     */
    public function getAlias(): null|string;

    /**
     * Retrieve the description of the {@see DefinitionInterface}.
     */
    public function getDescription(): string;

    /**
     * Retrieve the mode of the {@see DefinitionInterface}.
     */
    public function getMode(): Mode;

    /**
     * Returns if the {@see DefinitionInterface} has been assigned a value by the parser.
     */
    public function exists(): bool;

    /**
     * Retrieve the value of the {@see DefinitionInterface} as specified by the user.
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
