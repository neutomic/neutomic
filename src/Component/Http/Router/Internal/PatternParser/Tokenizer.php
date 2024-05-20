<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Internal\PatternParser;

use function array_filter;
use function str_split;

/**
 * @internal
 */
enum Tokenizer
{
    /**
     * @return array<int, Token>
     *
     * @internal
     */
    public static function tokenize(string $pattern): array
    {
        $tokens = [];
        $buffer = '';
        foreach (str_split($pattern) as $byte) {
            if (TokenType::isSpecialToken($byte)) {
                $tokens[] = new Token(TokenType::String, $buffer);
                $buffer = '';
                $tokens[] = new Token(TokenType::from($byte), $byte);
            } else {
                $buffer .= $byte;
            }
        }

        if ($buffer !== '') {
            $tokens[] = new Token(TokenType::String, $buffer);
        }

        return array_filter(
            $tokens,
            static fn (Token $t): bool => !($t->getType() === TokenType::String && $t->getValue() === '')
        );
    }
}
