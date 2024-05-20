<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Response;

use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;

use function strlen;

/**
 * Create a new text response.
 *
 * @param string $text The text content.
 *
 * @return ResponseInterface The response.
 */
function text(string $text): ResponseInterface
{
    $body = Body::fromString($text);
    $length = strlen($text);

    return Response::fromStatusCode(StatusCode::OK)
        ->withHeader('Content-Type', 'text/plain; charset=utf-8')
        ->withHeader('Content-Length', (string) $length)
        ->withBody($body);
}
