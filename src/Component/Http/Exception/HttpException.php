<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\StatusCode;
use Throwable;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * The status code.
     */
    protected StatusCode $statusCode;

    /**
     * Response headers.
     *
     * @var array<non-empty-string, non-empty-list<non-empty-string>>
     */
    protected array $headers;

    /**
     * Creates a new HTTP exception.
     *
     * @param StatusCode $statusCode The status code.
     * @param array<non-empty-string, non-empty-list<non-empty-string>> $headers Response headers.
     */
    public function __construct(StatusCode $statusCode, array $headers = [], string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $statusCode->value, $previous);

        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
