<?php

declare(strict_types=1);

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
}
