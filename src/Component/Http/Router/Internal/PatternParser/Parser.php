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

use Neu\Component\Http\Exception\RuntimeException;

use function array_merge;
use function array_shift;
use function var_export;

/**
 * @internal
 */
enum Parser
{
    /**
     * @param non-empty-string $pattern
     *
     * @throws RuntimeException
     */
    public static function parse(string $pattern): PatternNode
    {
        $tokens = Tokenizer::tokenize($pattern);

        [$node, $tokens] = self::parseImpl($tokens);

        if ($tokens !== []) {
            throw new RuntimeException('Tokens remaining at end of expression: ' . var_export($tokens, true));
        }

        return $node;
    }

    /**
     * @param list<Token> $tokens
     *
     * @throws RuntimeException
     *
     * @return array{PatternNode, list<Token>}
     */
    private static function parseImpl(array $tokens, bool $recursive = false): array
    {
        $nodes = [];

        while ($tokens !== []) {
            $token = array_shift($tokens);
            $type = $token->type;
            $text = $token->value;

            if ($type === TokenType::OpenBrace) {
                [$node, $tokens] = self::parseParameter($tokens);
                $nodes[] = $node;
                $token = array_shift($tokens);

                if (!$token instanceof Token) {
                    throw new RuntimeException('Expected closing brace after parameter, got null');
                }

                if ($token->getType() !== TokenType::CloseBrace) {
                    throw new RuntimeException('Expected closing brace after parameter, got ' . $token->toString());
                }

                continue;
            }

            if ($type === TokenType::OpenBracket) {
                [$node, $tokens] = self::parseImpl($tokens, true);

                $nodes[] = new OptionalNode($node);
                $token = array_shift($tokens);

                if (!$token instanceof Token) {
                    throw new RuntimeException('Expected closing brace after parameter, got null');
                }

                if ($token->getType() !== TokenType::CloseBracket) {
                    throw new RuntimeException('Expected closing brace after parameter, got ' . $token->toString());
                }

                continue;
            }

            if ($recursive && $type === TokenType::CloseBracket) {
                return [new PatternNode($nodes), array_merge([new Token($type, $text)], $tokens)];
            }

            if ($type !== TokenType::String) {
                throw new RuntimeException('Unexpected token type: ' . $type->toString());
            }

            $nodes[] = new LiteralNode($text);
        }

        return [new PatternNode($nodes), $tokens];
    }

    /**
     * @param list<Token> $tokens
     *
     * @throws RuntimeException
     *
     * @return array{ParameterNode, list<Token>}
     */
    private static function parseParameter(iterable $tokens): array
    {
        $token = array_shift($tokens);
        if (!$token instanceof Token) {
            throw new RuntimeException('Expected parameter to start with a name, got null');
        }

        if ($token->getType() !== TokenType::String) {
            throw new RuntimeException('Expected parameter to start with a name, got ' . $token->toString());
        }

        $name = $token->value;

        $token = current($tokens);
        if (false === $token) {
            return [new ParameterNode($name, null), []];
        }

        if ($token->getType() === TokenType::CloseBrace) {
            return [new ParameterNode($name, null), [$token]];
        }

        if ($token->getType() !== TokenType::Colon) {
            throw new RuntimeException('Expected parameter name "' . $name . '" to be followed by ":" or "}", got ' . $token->toString());
        }

        array_shift($tokens); // consume the colon

        $regexp = '';
        $depth = 0;
        while ($tokens !== []) {
            $token = array_shift($tokens);
            if ($token->getType() === TokenType::OpenBrace) {
                ++$depth;
            } elseif ($token->getType() === TokenType::CloseBrace) {
                if ($depth === 0) {
                    break;
                }
                --$depth;
            }

            $regexp .= $token->getValue();
        }

        if ($depth !== 0) {
            throw new RuntimeException('Unbalanced braces in regexp');
        }

        return [new ParameterNode($name, $regexp), $tokens];
    }
}
