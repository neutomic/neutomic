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

/**
 * This interface represents a single HTTP trailer.
 *
 * A trailer is similar to an HTTP header but is sent after the HTTP message body.
 *
 * Trailers can be used to include additional metadata that isn't known prior to the message being sent.
 *
 * @psalm-type Field = non-empty-string
 * @psalm-type Value = non-empty-list<non-empty-string>
 */
interface TrailerInterface
{
    /**
     * Retrieves the field name of the trailer.
     *
     * This method returns the name of the trailer field.
     *
     * @return Field The name of the trailer field.
     */
    public function getField(): string;

    /**
     * Retrieves the values associated with the trailer field.
     *
     * @return Value A list of strings representing the values of the trailer field.
     */
    public function getValue(): array;
}
