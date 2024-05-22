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

use function str_split;

/**
 * @internal
 */
enum Tokenizer
{
    /**
     * @return list<Token>
     *
     * @internal
     */
    public static function tokenize(string $pattern): array
    {
        $tokens = [];
        $buffer = '';
        foreach (str_split($pattern) as $byte) {
            if (TokenType::isSpecialToken($byte)) {
                if ($buffer !== '') {
                    $tokens[] = new Token(TokenType::String, $buffer);
                    $buffer = '';
                }

                $tokens[] = new Token(TokenType::from($byte), $byte);
            } else {
                $buffer .= $byte;
            }
        }

        if ($buffer !== '') {
            $tokens[] = new Token(TokenType::String, $buffer);
        }

        return $tokens;
    }
}
