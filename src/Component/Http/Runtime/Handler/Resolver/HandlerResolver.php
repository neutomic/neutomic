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

namespace Neu\Component\Http\Runtime\Handler\Resolver;

use Neu\Component\Http\Exception\HandlerNotFoundException;
use Neu\Component\Http\Exception\InvalidHandlerException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Resolves the handler for a given request.
 *
 * @psalm-suppress MixedAssignment
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class HandlerResolver implements HandlerResolverInterface
{
    private null|HandlerInterface $fallback;

    public function __construct(null|HandlerInterface $fallback = null)
    {
        $this->fallback = $fallback;
    }

    /**
     * @inheritDoc
     */
    public function resolve(RequestInterface $request): HandlerInterface
    {
        if (!$request->hasAttribute(HandlerInterface::class)) {
            if ($this->fallback instanceof HandlerInterface) {
                return $this->fallback;
            }

            throw HandlerNotFoundException::forRequest($request);
        }

        $handler = $request->getAttribute(HandlerInterface::class);
        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        throw InvalidHandlerException::forHandler($handler);
    }
}
