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

namespace Neu\Component\DependencyInjection\Configuration;

use Countable;
use IteratorAggregate;
use Neu\Component\DependencyInjection\Exception\InvalidEntryException;
use Neu\Component\DependencyInjection\Exception\MissingEntryException;
use Psl\Type\TypeInterface;

/**
 * @extends IteratorAggregate<array-key, mixed>
 */
interface DocumentInterface extends Countable, IteratorAggregate
{
    /**
     * Return whether the document is strict.
     *
     * A strict document will not coerce values into other types, and will
     * throw an exception if the value is not of the expected type.
     */
    public function isStrict(): bool;

    /**
     * Return whether an entry with the given index exists within this document.
     *
     * @param array-key $index
     */
    public function has(string|int $index): bool;

    /**
     * Retrieve the entry value using its index.
     *
     * @param array-key $index
     *
     * @throws MissingEntryException If the entry is not found does not exist.
     */
    public function get(string|int $index): mixed;

    /**
     * Retrieve the entry value using its index.
     *
     * @template EntryType
     *
     * @param array-key $index
     * @param TypeInterface<EntryType> $type
     *
     * @throws InvalidEntryException If the entry value cannot be converted into the given type.
     * @throws MissingEntryException If the entry is not found does not exist.
     *
     * @return EntryType
     */
    public function getOfType(string|int $index, TypeInterface $type): mixed;

    /**
     * Retrieve the entry value using its index or return the default value.
     *
     * @template EntryType
     * @template DefaultType
     *
     * @param array-key $index The index of the entry.
     * @param TypeInterface<EntryType> $type The type to check against.
     * @param DefaultType $default The default value to return if the entry does not exist.
     *
     * @throws InvalidEntryException If the entry value cannot be converted into the given type.
     *
     * @return EntryType|DefaultType
     */
    public function getOfTypeOrDefault(string|int $index, TypeInterface $type, mixed $default): mixed;

    /**
     * Return whether the entry value using its index is of the given type.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @template EntryType
     *
     * @param array-key $index The index of the entry.
     * @param TypeInterface<EntryType> $type The type to check against.
     */
    public function isOfType(string|int $index, TypeInterface $type): bool;

    /**
     * Retrieve the entry document value using its index.
     *
     * @param array-key $index
     * @param bool|null $strict Whether the retrieved document should be strict,
     *                          or null to inherit the current document's strictness.
     *
     * @throws MissingEntryException If the entry is not found does not exist.
     * @throws InvalidEntryException If the entry value cannot be converted into a document.
     *
     * @return DocumentInterface
     */
    public function getDocument(string|int $index, null|bool $strict = null): DocumentInterface;

    /**
     * Return whether the entry value using its index is a document.
     *
     * If the entry does not exist, this method MUST return false.
     *
     * @param array-key $index
     */
    public function isDocument(string|int $index): bool;

    /**
     * Return a list of all entry indices present in the current document.
     *
     * @return list<array-key>
     */
    public function getIndices(): array;

    /**
     * Retrieve all entries within this document.
     *
     * @return array<array-key, mixed>
     */
    public function getAll(): array;

    /**
     * Combine the current document entries with the given document entries using the given strategy.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries set.
     *
     * The resulting document MUST NOT be strict if either the current document
     * or the given document is strict.
     *
     * @param DocumentInterface $document The document to combine with the current document.
     * @param CombineStrategy $strategy The strategy to use when combining the entries.
     *
     * @return static The combined document.
     */
    public function combine(DocumentInterface $document, CombineStrategy $strategy): static;

    /**
     * Replace the current document entries with the given document entries.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries set.
     *
     * The resulting document MUST NOT be strict if either the current document
     * or the given document is strict.
     *
     * @param DocumentInterface $document The document to replace the current document entries with.
     * @param bool $recursive Whether to replace the entries recursively.
     *
     * @return static The document with the replaced entries.
     *
     * @see https://www.php.net/manual/en/function.array-replace.php
     * @see https://www.php.net/manual/en/function.array-replace-recursive.php
     */
    public function replace(DocumentInterface $document, bool $recursive = true): static;

    /**
     * Merge the current document entries with the given document entries.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the configuration, and MUST return an instance that has the
     * new configuration entries set.
     *
     * The resulting document MUST NOT be strict if either the current document
     * or the given document is strict.
     *
     * @param DocumentInterface $document The document to merge with the current document.
     * @param bool $recursive Whether to merge the entries recursively.
     *
     * @return static The merged document.
     *
     * @see https://www.php.net/manual/en/function.array-merge.php
     * @see https://www.php.net/manual/en/function.array-merge-recursive.php
     */
    public function merge(DocumentInterface $document, bool $recursive = true): static;
}
