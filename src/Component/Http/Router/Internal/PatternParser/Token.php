<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Internal\PatternParser;

use function sprintf;

/**
 * @internal
 */
final readonly class Token
{
    public function __construct(private TokenType $type, private string $value)
    {
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
