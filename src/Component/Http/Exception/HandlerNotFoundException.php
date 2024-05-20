<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\StatusCode;

final class HandlerNotFoundException extends RuntimeException implements HttpExceptionInterface
{
    public static function forRequest(RequestInterface $request): self
    {
        return new self(
            'Unable to resolve handler for path ' . $request->getUri()->getPath() . '. Did you forget to configure a handler to the route?',
        );
    }

    public function getStatusCode(): StatusCode
    {
        return StatusCode::NotFound;
    }

    public function getHeaders(): array
    {
        return [];
    }
}
