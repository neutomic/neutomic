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
