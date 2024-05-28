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

namespace Neu\Component\Http\Router\Exception;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Message\UriInterface;
use Throwable;

final class RouteNotFoundHttpException extends HttpException
{
    public function __construct(string $message = '', null|Throwable $previous = null)
    {
        parent::__construct(StatusCode::NotFound, [], $message, $previous);
    }

    public static function create(Method $method, UriInterface $uri): self
    {
        return new self('No route found for "' . $method->value . ' ' . $uri->getPath() . '".');
    }
}
