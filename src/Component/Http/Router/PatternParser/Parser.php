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

use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Router\PatternParser\Node\LiteralNode;
use Neu\Component\Http\Router\PatternParser\Node\OptionalNode;
use Neu\Component\Http\Router\PatternParser\Node\ParameterNode;
use Neu\Component\Http\Router\PatternParser\Node\PatternNode;

use function array_shift;
use function var_export;

/**
 * Parser for URI patterns.
 *
 * This enum provides methods for parsing URI patterns into a structured format
 * using a series of tokens. The resulting structure can be used within the router
 * component for matching and handling routes.
 */
enum Parser
{
    /**
     * Parse a URI pattern.
     *
     * This method tokenizes the given pattern and converts it into a structured
     * format represented by a {@see PatternNode}.
     *
     * @param non-empty-string $pattern The URI pattern to parse.
     *
     * @throws RuntimeException If there are remaining tokens after parsing or if
     *                          the pattern contains unexpected tokens.
     *
     * @return PatternNode The root node of the parsed pattern structure.
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
     * Recursive implementation of the parsing logic.
     *
     * This method processes the tokens recursively, handling nested structures
     * such as optional segments and parameters.
     *
     * @param list<Token> $tokens The list of tokens to parse.
     * @param bool $recursive Whether the parsing is within a recursive context.
     *
     * @throws RuntimeException If unexpected tokens are encountered or if
     *                          structures are unbalanced.
     *
     * @return array{PatternNode, list<Token>} A tuple containing the parsed node
     *                                         and the remaining tokens.
     */
    private static function parseImpl(array $tokens, bool $recursive = false): array
    {
        $nodes = [];

        while ($tokens !== []) {
            /** @var Token $token */
            $token = current($tokens);

            if ($token->type === TokenType::OpenBrace) {
                array_shift($tokens); // consume the open brace
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

            if ($token->type === TokenType::OpenBracket) {
                array_shift($tokens); // consume the open bracket
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

            if ($recursive && $token->type === TokenType::CloseBracket) {
                return [new PatternNode(...$nodes), $tokens];
            }

            if ($token->type !== TokenType::String) {
                throw new RuntimeException('Unexpected token type: ' . $token->type->toString());
            }

            array_shift($tokens); // consume the token

            $nodes[] = new LiteralNode($token->value);
        }

        return [new PatternNode(...$nodes), $tokens];
    }

    /**
     * Parse a parameter from the tokens.
     *
     * This method extracts a parameter from the list of tokens, including its
     * name and optional regular expression constraint.
     *
     * @param list<Token> $tokens The list of tokens to parse.
     *
     * @throws RuntimeException If the parameter is not well-formed or if
     *                          unexpected tokens are encountered.
     *
     * @return array{ParameterNode, list<Token>} A tuple containing the parsed
     *                                           parameter node and the remaining tokens.
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
            return [new ParameterNode($name, null), $tokens];
        }

        if ($token->getType() !== TokenType::Colon) {
            throw new RuntimeException('Expected parameter name "' . $name . '" to be followed by ":" or "}", got ' . $token->toString());
        }

        array_shift($tokens); // consume the colon

        $regexp = '';
        $depth = 0;
        while ($tokens !== []) {
            $token = current($tokens);
            if ($token->getType() === TokenType::OpenBrace) {
                ++$depth;
            } elseif ($token->getType() === TokenType::CloseBrace) {
                if ($depth === 0) {
                    break;
                }

                --$depth;
            }

            array_shift($tokens); // consume the token

            $regexp .= $token->getValue();
        }

        if ($depth !== 0) {
            throw new RuntimeException('Unbalanced braces in regexp');
        }

        $regexp = '' === $regexp ? null : $regexp;

        return [new ParameterNode($name, $regexp), $tokens];
    }
}
