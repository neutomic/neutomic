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
