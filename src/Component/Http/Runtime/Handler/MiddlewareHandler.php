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

namespace Neu\Component\Http\Runtime\Handler;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Middleware\MiddlewareInterface;

final readonly class MiddlewareHandler implements HandlerInterface
{
    public function __construct(
        private MiddlewareInterface $middleware,
        private HandlerInterface $next
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($context, $request, $this->next);
    }
}
