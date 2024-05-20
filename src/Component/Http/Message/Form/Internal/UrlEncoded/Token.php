<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form\Internal\UrlEncoded;

final readonly class Token
{
    public TokenType $type;
    public string $value;

    public function __construct(TokenType $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}
