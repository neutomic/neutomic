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

use Neu\Component\Http\Exception\LogicException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Exception\HandlerNotFoundHttpException;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

use function get_debug_type;

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
    #[\Override]
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return $this->resolve($context, $request)->handle($context, $request);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function resolve(Context $context, RequestInterface $request): HandlerInterface
    {
        if (!$request->hasAttribute(HandlerInterface::class)) {
            if ($this->fallback instanceof HandlerInterface) {
                return $this->fallback;
            }

            throw new HandlerNotFoundHttpException(
                message: 'Unable to resolve handler for path "' . $request->getUri()->getPath() . '". Did you forget to configure a handler to the route?',
            );
        }

        $handler = $request->getAttribute(HandlerInterface::class);
        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        throw new LogicException(
            message: 'Invalid handler provided. Expected an instance of "' . HandlerInterface::class . '", got "' . get_debug_type($handler) . '".',
        );
    }
}
