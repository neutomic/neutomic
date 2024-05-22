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

interface ExchangeInterface extends MessageInterface
{
    /**
     * Retrieves the HTTP protocol version of the exchange.
     *
     * @return ProtocolVersion The HTTP protocol version of the exchange.
     */
    public function getProtocolVersion(): ProtocolVersion;

    /**
     * Returns a new instance of the exchange with the specified HTTP protocol version.
     *
     * @param ProtocolVersion $version The HTTP protocol version to set.
     *
     * @return static A new instance with the updated protocol version.
     */
    public function withProtocolVersion(ProtocolVersion $version): static;

    /**
     * Retrieves all trailers currently associated with the message.
     *
     * @return array<non-empty-string, TrailerInterface> An associative array of trailers,
     *                                                   where each key is a trailer field name and each value is a TrailerInterface representing the trailer's values.
     */
    public function getTrailers(): array;

    /**
     * Checks for the presence of a trailer by its field name.
     *
     * @param non-empty-string $field The field name of the trailer to check.
     *
     * @return bool True if the trailer exists, false otherwise.
     */
    public function hasTrailer(string $field): bool;

    /**
     * Retrieves the trailer associated with the specified field name.
     *
     * This method performs a case-sensitive search and returns the trailer if found. If no matching trailer
     * is found, the method returns null.
     *
     * @param non-empty-string $field The field name of the trailer to retrieve.
     *
     * @return null|TrailerInterface The trailer for the specified field name, or null if not found.
     */
    public function getTrailer(string $field): null|TrailerInterface;

    /**
     * Returns a new instance with the specified trailer added or replaced.
     *
     * This method adds a new trailer or replaces an existing trailer identified by the field name.
     *
     * @param TrailerInterface $trailer The trailer to add or replace.
     *
     * @return static A new instance with the specified trailer added or replaced.
     */
    public function withTrailer(TrailerInterface $trailer): static;

    /**
     * Returns a new instance without the specified trailer.
     *
     * This method removes a trailer identified by the given field name.
     *
     * @param non-empty-string $field The field name of the trailer to remove.
     *
     * @return static A new instance without the specified trailer.
     */
    public function withoutTrailer(string $field): static;
}
