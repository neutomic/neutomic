<?php

declare(strict_types=1);

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
