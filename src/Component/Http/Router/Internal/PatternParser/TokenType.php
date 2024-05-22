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

namespace Neu\Component\Http\Router\Internal\PatternParser;

use function in_array;

/**
 * @internal
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

    public static function isSpecialToken(string $byte): bool
    {
        return in_array($byte, self::SPECIAL_TOKENS, true);
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return match ($this) {
            self::String => 'String "?"',
            self::Colon => 'Colon ":"',
            self::OpenBrace => 'OpenBrace "{"',
            self::CloseBrace => 'CloseBrace "}"',
            self::OpenBracket => 'OpenBracket "["',
            self::CloseBracket => 'CloseBracket "]"',
        };
    }
}
