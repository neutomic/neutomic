<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\StatusCode;

interface HttpExceptionInterface extends ExceptionInterface
{
    /**
     * Returns the status code.
     *
     * @return StatusCode An HTTP response status code
     */
    public function getStatusCode(): StatusCode;

    /**
     * Returns response headers.
     *
     * @return array<string, non-empty-string|list<non-empty-string>> Response headers
     */
    public function getHeaders(): array;
}
