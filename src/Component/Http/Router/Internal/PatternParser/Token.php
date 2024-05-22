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

use function sprintf;

/**
 * @internal
 */
final readonly class Token
{
    public TokenType $type;

    /**
     * @var non-empty-string
     */
    public string $value;

    /**
     * @param non-empty-string $value
     */
    public function __construct(TokenType $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return sprintf('"%s" (%s)', $this->value, $this->type->value);
    }
}
