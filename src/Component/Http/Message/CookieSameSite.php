<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message;

/**
 * Enum representing the cookie Same-Site values.
 *
 * @link https://tools.ietf.org/html/draft-west-first-party-cookies-07#section-3.1
 */
enum CookieSameSite: string
{
    case Lax = 'Lax';
    case Strict = 'Strict';
}
