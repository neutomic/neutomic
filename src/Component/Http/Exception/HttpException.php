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

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\StatusCode;
use Throwable;
use Override;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * The status code.
     */
    protected StatusCode $statusCode;

    /**
     * Response headers.
     *
     * @var array<non-empty-string, non-empty-string|non-empty-list<non-empty-string>>
     */
    protected array $headers;

    /**
     * Creates a new HTTP exception.
     *
     * @param StatusCode $statusCode The status code.
     * @param array<non-empty-string, non-empty-string|non-empty-list<non-empty-string>> $headers Response headers.
     */
    public function __construct(StatusCode $statusCode, array $headers = [], string $message = '', null|Throwable $previous = null)
    {
        parent::__construct($message, $statusCode->value, $previous);

        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
