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

namespace Neu\Component\Http\Router\PatternParser;

use function in_array;

/**
 * Enum representing the various token types used in the URI pattern parsing process.
 *
 * This enum defines the different token types that can be encountered while parsing
 * URI patterns within the router component. It provides methods to identify special
 * tokens and to obtain a string representation of each token type.
 */
enum TokenType: string
{
    private const array SPECIAL_TOKENS = [
        self::Colon->value,
        self::OpenBrace->value,
        self::CloseBrace->value,
        self::OpenBracket->value,
        self::CloseBracket->value,
    ];

    case String = 'string';
    case Colon = ':';
    case OpenBrace = '{';
    case CloseBrace = '}';
    case OpenBracket = '[';
    case CloseBracket = ']';

    /**
     * Check if a given byte represents a special token.
     *
     * This method checks whether the provided byte corresponds to one of the
     * special tokens defined in the enum.
     *
     * @param string $byte The byte to check.
     *
     * @return bool True if the byte is a special token, false otherwise.
     */
    public static function isSpecialToken(string $byte): bool
    {
        return in_array($byte, self::SPECIAL_TOKENS, true);
    }

    /**
     * Get the string representation of the token type.
     *
     * This method returns a string representation of the token type, primarily
     * intended for debugging purposes. Each token type is represented by a
     * descriptive string.
     *
     * @return non-empty-string The string representation of the token type.
     */
    public function toString(): string
    {
        return $this->name . ' "' . $this->value . '"';
    }
}
