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
 * Create a new XML response.
 *
 * @param string $xml The XML content.
 *
 * @return ResponseInterface The response.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
function xml(string $xml): ResponseInterface
{
    $body = Body::fromString($xml);
    $length = strlen($xml);

    return Response::fromStatusCode(StatusCode::OK)
        ->withHeader('Content-Type', 'application/xml; charset=utf-8')
        ->withHeader('Content-Length', (string) $length)
        ->withBody($body);
}
