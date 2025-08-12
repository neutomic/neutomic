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

namespace Neu\Component\Http\Message\Form;

use Override;

/**
 * Interface for form data.
 *
 * This interface is the result of calling {@see ParserInterface::parse}.
 *
 * The form data will be loaded entirely into memory. If the data contains
 * large amounts of data, it is recommended to use {@see StreamedParserInterface::parseStreamed()}
 * to parse the form incrementally, which is better for memory usage.
 */
interface FormInterface extends StreamedFormInterface
{
    /**
     * Retrieves all fields from the parsed form data.
     *
     * @return list<FieldInterface> A list of {@see FieldInterface} implementations.
     */
    #[Override]
    public function getFields(): array;

    /**
     * Retrieves all file fields from the parsed form data.
     *
     * @return list<FileInterface> A list of {@see FileInterface} implementations.
     */
    public function getFiles(): array;

    /**
     * Retrieves all fields that have a specific name.
     *
     * @param non-empty-string $name The name to filter fields by.
     *
     * @return list<FieldInterface> A list of {@see FieldInterface} implementations with the specified name.
     */
    public function getFieldsByName(string $name): array;

    /**
     * Retrieves the first field that has a specific name.
     *
     * @param non-empty-string $name The name to filter fields by.
     *
     * @return FieldInterface|null The first {@see FieldInterface} implementation with the specified name, or null if not found.
     */
    public function getFirstFieldByName(string $name): null|FieldInterface;

    /**
     * Checks if there are fields with a specific name.
     *
     * @param non-empty-string $name The name to check for.
     *
     * @return bool True if there are fields with the specified name, false otherwise.
     */
    public function hasFieldWithName(string $name): bool;
}
