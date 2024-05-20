<?php

declare(strict_types=1);

namespace Neu\Component\Http\Exception;

use Neu\Component\Http\Message\StatusCode;
use Throwable;

final class ForbiddenException extends HttpException
{
    public function __construct(array $headers = [], string $message = 'Forbidden', ?Throwable $previous = null)
    {
        parent::__construct(StatusCode::Forbidden, $headers, $message, $previous);
    }
}
