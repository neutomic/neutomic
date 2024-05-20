<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Response;

use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Exception\InvalidArgumentException;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Psl\Json;

use function strlen;

/**
 * Create a new JSON response.
 *
 * @param string $json The JSON content.
 *
 * @throws InvalidArgumentException If the JSON content is invalid.
 *
 * @return ResponseInterface The response.
 */
function json(mixed $json): ResponseInterface
{
    try {
        $json = Json\encode($json);
    } catch (Json\Exception\ExceptionInterface $e) {
        throw new InvalidArgumentException('Failed to encode the provided data to JSON.', previous: $e);
    }

    $body = Body::fromString($json);
    $length = strlen($json);

    return Response::fromStatusCode(StatusCode::OK)
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withHeader('Content-Length', (string) $length)
        ->withBody($body);
}
