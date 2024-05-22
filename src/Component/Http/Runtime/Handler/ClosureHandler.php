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
 */
final readonly class ClosureHandler implements HandlerInterface
{
    /**
     * @var Closure(Context, RequestInterface): ResponseInterface
     */
    private Closure $closure;

    /**
     * @param (Closure(Context, RequestInterface): ResponseInterface) $closure
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
