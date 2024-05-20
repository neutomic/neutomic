<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;

final class NotFoundHttpException extends RuntimeException implements HttpExceptionInterface
{
    public static function create(Method $method, UriInterface $uri): self
    {
        return new self('No route found for "' . $method->value . ' ' . $uri->getPath() . '".');
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): StatusCode
    {
        return StatusCode::NotFound;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return [];
    }
}
