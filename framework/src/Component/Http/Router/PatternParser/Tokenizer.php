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

use function str_split;

/**
 * Tokenizer for parsing URI patterns into tokens.
 *
 * This enum provides a method for converting a URI pattern string into a list
 * of tokens. The tokens are instances of the {@see Token} class and are used
 * in the pattern parsing process within the router component.
 */
enum Tokenizer
{
    /**
     * Tokenize a URI pattern string.
     *
     * This method splits the given pattern string into its constituent tokens,
     * identifying special tokens as defined by {@see TokenType}. It returns a
     * list of {@see Token} instances representing the parsed tokens.
     *
     * @param string $pattern The URI pattern string to tokenize.
     *
     * @return list<Token> A list of tokens parsed from the pattern string.
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
