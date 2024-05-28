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

namespace Neu\Component\Http\Runtime\Exception;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\StatusCode;
use Throwable;

final class HandlerNotFoundHttpException extends HttpException
{
    public function __construct(string $message = '', null|Throwable $previous = null)
    {
        parent::__construct(StatusCode::NotFound, [], $message, $previous);
    }

    public static function forRequest(RequestInterface $request): self
    {
        return new self(
            'Unable to resolve handler for path "' . $request->getUri()->getPath() . '", did you forget to configure a handler to the route?',
        );
    }
}
