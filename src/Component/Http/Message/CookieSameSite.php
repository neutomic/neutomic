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
