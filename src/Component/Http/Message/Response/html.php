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

use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;

use function strlen;

/**
 * Create a new HTML response.
 *
 * @param string $html The HTML content.
 *
 * @return ResponseInterface The response.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
function html(string $html): ResponseInterface
{
    $body = Body::fromString($html);
    $length = strlen($html);

    return Response::fromStatusCode(StatusCode::OK)
        ->withHeader('Content-Type', 'text/html; charset=utf-8')
        ->withHeader('Content-Length', (string) $length)
        ->withBody($body);
}
