<?php

declare(strict_types=1);

namespace Neu\Component\Http\Message\Response;

use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;

function redirect(UriInterface|string $location, StatusCode $statusCode = StatusCode::PermanentRedirect): ResponseInterface
{
    if ($location instanceof UriInterface) {
        $location = $location->toString();
    }

    return Response::fromStatusCode($statusCode)
        ->withHeader('Location', $location);
}
