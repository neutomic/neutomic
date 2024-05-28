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

namespace Neu\Component\Http\Message\Response;

use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;

/**
 * Create a new redirect response.
 *
 * @param UriInterface|non-empty-string $location The location to redirect to.
 * @param StatusCode $statusCode The status code.
 *
 * @return ResponseInterface The response.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
function redirect(UriInterface|string $location, StatusCode $statusCode = StatusCode::PermanentRedirect): ResponseInterface
{
    if ($location instanceof UriInterface) {
        $location = $location->toString();
    }

    $location = $location === '' ? '/' : $location;

    return Response::fromStatusCode($statusCode)
        ->withHeader('Location', $location);
}
