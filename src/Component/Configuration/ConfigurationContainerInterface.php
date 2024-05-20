<?php

declare(strict_types=1);

namespace Neu\Component\Configuration;

use BackedEnum;
use Countable;
use IteratorAggregate;
use Psl\Type\TypeInterface;

/**
 * @extends IteratorAggregate<array-key, mixed>
 */
interface ConfigurationContainerInterface extends Countable, IteratorAggregate
{
    /**
     * Return whether the container is strict.
     *
     * A strict container will not coerce values into other types, and will
     * throw an exception if the value is not of the expected type.
     */
    public function isStrict(): bool;

    /**
     * Return whether an entry with the given index exists within this container.
     *
     * @param array-key $index
     */
    public function has(string|int $index): bool;

    /**
     * Retrieve the entry value using its index.
     *
     * @param array-key $index
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function get(string|int $index): mixed;

    /**
     * Retrieve the entry value using its index.
     *
     * @template O
     *
     * @param array-key $index
     * @param TypeInterface<O> $type
     *
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into the given type.
     *
     * @return O
     */
    public function getOfType(string|int $index, TypeInterface $type): mixed;

    /**
     * Retrieve the entry value using its index or return the default value.
     *
     * @template O
     * @template D
     *
     * @param array-key $index The index of the entry.
     * @param TypeInterface<O> $type The type to check against.
     * @param D $default The default value to return if the entry does not exist.
     *
     * @return O|D
     */
    public function getOfTypeOrDefault(string|int $index, TypeInterface $type, mixed $default): mixed;

    /**
     * Return whether the entry value using its index is of the given type.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @template O
     *
     * @param array-key $index The index of the entry.
     * @param TypeInterface<O> $type The type to check against.
     */
    public function isOfType(string|int $index, TypeInterface $type): bool;

    /**
     * Retrieve the entry string value using its index.
     *
     * @param array-key $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a string.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function getString(string|int $index): string;

    /**
     * Return whether the entry value using its index is a string.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isString(string|int $index): bool;

    /**
     * Retrieve the entry integer value using its index.
     *
     * @param array-key $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into an integer.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function getInt(string|int $index): int;

    /**
     * Return whether the entry value using its index is an integer.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isInt(string|int $index): bool;

    /**
     * Retrieve the entry boolean value using its index.
     *
     * @param array-key $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a boolean.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function getBool(string|int $index): bool;

    /**
     * Return whether the entry value using its index is a boolean.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isBool(string|int $index): bool;

    /**
     * Retrieve the entry float value using its index.
     *
     * @param array-key $index
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a float.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     */
    public function getFloat(string|int $index): float;

    /**
     * Return whether the entry value using its index is a float.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isFloat(string|int $index): bool;

    /**
     * Retrieve the entry value using its index and return one of the given values.
     *
     * @template Tc of scalar
     *
     * @param array-key $index
     * @param list<Tc> $values
     *
     * @throws Exception\InvalidEntryException If the entry value is not one of the given values.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     *
     * @return Tc
     */
    public function oneOf(string|int $index, array $values): mixed;

    /**
     * Return whether the entry value using its index is one of the given values.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     * @param list<scalar> $values
     */
    public function isOneOf(string|int $index, array $values): bool;

    /**
     * Retrieve the entry enum value using its index.
     *
     * @template T of BackedEnum
     *
     * @param array-key $index
     * @param class-string<T> $enum
     *
     * @throws Exception\InvalidEntryException If the entry value is not one of the enum values.
     * @throws Exception\MissingEntryException If the entry is not found does not exist.
     *
     * @return T
     */
    public function getEnum(string|int $index, string $enum): BackedEnum;

    /**
     * Return whether the entry value using its index is an enum.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     * @param class-string<BackedEnum> $enum
     */
    public function isEnum(string|int $index, string $enum): bool;

    /**
     * Retrieve the entry container value using its index.
     *
     * @param array-key $index
     * @param bool|null $strict Whether the retrieved container should be strict,
     *                          or null to inherit the current container's strictness.
     *
     * @throws Exception\InvalidEntryException If the entry value cannot be converted into a container.
     *
     * @return ConfigurationContainerInterface
     */
    public function getContainer(string|int $index, ?bool $strict = null): ConfigurationContainerInterface;

    /**
     * Return whether the entry value using its index is a container.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isContainer(string|int $index): bool;

    /**
     * Merge the current container with the given container.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries merged.
     *
     * The resulting container MUST NOT be strict if either the current container
     * or the given container is strict.
     *
     * @param ConfigurationContainerInterface $container
     *
     * @return static
     */
    public function merge(ConfigurationContainerInterface $container): static;

    /**
     * Return a list of all entry indices present in the current container.
     *
     * @return list<array-key>
     */
    public function getIndices(): array;

    /**
     * Retrieve all entries within this container.
     *
     * @return array<array-key, mixed>
     */
    public function getAll(): array;
}
