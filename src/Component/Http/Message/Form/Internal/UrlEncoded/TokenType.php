<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Form\Internal\UrlEncoded;

enum TokenType: string
{
    case String = 'string';
    case Equals = '=';
    case Ampersand = '&';
}
