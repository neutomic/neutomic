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

namespace Neu\Component\Http\Message;

use InvalidArgumentException;

interface MessageInterface
{
    /**
     * Retrieves all headers associated with the message.
     *
     * This method returns a dictionary where each key is a header name and each value is a list of strings representing the
     * header's multiple values. The method preserves the case of the header names as originally provided.
     *
     * @return array<non-empty-string, non-empty-list<non-empty-string>> A dictionary of all headers in the message,
     *                                                                   where each key is a header name and each value is a list of string values for that header.
     */
    public function getHeaders(): array;

    /**
     * Checks for the presence of a specific header by name, using a case-insensitive comparison.
     *
     * @param non-empty-string $name The header name to check, case-insensitive.
     *
     * @return bool True if the header exists, false otherwise.
     */
    public function hasHeader(string $name): bool;

    /**
     * Retrieves all values of a specific header by name, if present.
     *
     * This method performs a case-insensitive search to find the header and returns a list of its values.
     * If no such header exists, the method returns null.
     *
     * @param non-empty-string $name The case-insensitive name of the header.
     *
     * @return null|non-empty-list<non-empty-string> A list of values for the specified header or null if the header does not exist.
     */
    public function getHeader(string $name): null|array;

    /**
     * Retrieves a single string composed of all values of a specific header, concatenated using a comma.
     *
     * This method concatenates all values of the specified header into a single string, using a comma to separate them.
     * If the header is not present, the method returns null. Note that not all header values are suitable for this
     * representation; for complex header structures, consider using {@see getHeader()} instead.
     *
     * @param non-empty-string $name The case-insensitive name of the header.
     *
     * @return null|non-empty-string A single string of concatenated header values, or null if the header is absent.
     */
    public function getHeaderLine(string $name): null|string;

    /**
     * Returns a new instance of the message with the specified header replaced with the provided value(s).
     *
     * This method replaces the existing values of a header with the new value(s) provided. If the header does not
     * exist, it is added. Header names are treated case-insensitively, but the case of the input name is preserved
     * in the new instance.
     *
     * @param non-empty-string $name The header name to replace, case-insensitive.
     * @param non-empty-string|non-empty-list<non-empty-string> $value The new value or values for the header.
     *
     * @throws InvalidArgumentException for invalid header names or values.
     *
     * @return static A new instance with the updated header.
     */
    public function withHeader(string $name, string|array $value): static;

    /**
     * Returns a new instance of the message with the specified header value(s) appended.
     *
     * This method appends new value(s) to an existing header without removing the previous values. If the header
     * does not previously exist, it is created. Header names are treated case-insensitively.
     *
     * @param non-empty-string $name The header name for which to append values, case-insensitive.
     * @param non-empty-string|non-empty-list<non-empty-string> $value The value or values to append.
     *
     * @throws InvalidArgumentException for invalid header names or values.
     *
     * @return static A new instance with the appended header values.
     */
    public function withAddedHeader(string $name, string|array $value): static;

    /**
     * Returns a new instance of the message without the specified header.
     *
     * This method removes all values of a header identified by the given name. Header names are treated
     * case-insensitively.
     *
     * @param non-empty-string $name The name of the header to remove, case-insensitive.
     *
     * @return static A new instance without the specified header.
     */
    public function withoutHeader(string $name): static;

    /**
     * Retrieves the body of the message.
     *
     * @return ?BodyInterface Returns the body of the message or null if the body has not been set.
     */
    public function getBody(): null|BodyInterface;

    /**
     * Returns a new instance with the specified message body.
     *
     * @param ?BodyInterface $body The new body of the message, or null to remove the body.
     *
     * @return static A new instance with the specified body.
     */
    public function withBody(null|BodyInterface $body): static;
}
