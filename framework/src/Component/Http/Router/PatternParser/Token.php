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

/**
 * Represents a token in the URI pattern parsing process.
 *
 * This class encapsulates a token, which consists of a type and a value,
 * used in the pattern parsing within the router component. Instances of this
 * class are immutable and read-only.
 */
final readonly class Token
{
    /**
     * The type of the token.
     */
    public TokenType $type;

    /**
     * The value of the token.
     *
     * @var non-empty-string
     */
    public string $value;

    /**
     * Create a new {@see Token} instance.
     *
     * @param TokenType $type The type of the token.
     * @param non-empty-string $value The value of the token.
     */
    public function __construct(TokenType $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Retrieve the type of the token.
     *
     * @return TokenType The type of the token.
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * Retrieve the value of the token.
     *
     * @return non-empty-string The value of the token.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the string representation of the token.
     *
     * This method returns a string that represents the token, including its
     * value and type, primarily intended for debugging purposes.
     *
     * @return string The string representation of the token.
     */
    public function toString(): string
    {
        return '"' . $this->value . '" (' . $this->type->toString() . ')';
    }
}
