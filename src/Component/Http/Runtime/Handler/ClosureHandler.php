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

use Closure;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;

/**
 * Handles a request using a closure.
 *
 * @psalm-type Handler = (Closure(Context, RequestInterface): ResponseInterface)
 */
final readonly class ClosureHandler implements HandlerInterface
{
    /**
     * The closure to handle the request.
     *
     * @var Handler
     */
    private Closure $closure;

    /**
     * Creates a new {@see ClosureHandler} instance.
     *
     * @param Handler $closure The closure to handle the request
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritDoc
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return ($this->closure)($context, $request);
    }
}
