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
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Exception\HandlerNotFoundHttpException;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Interface for resolving a handler based on the given HTTP request.
 *
 * This interface is used within the runtime to determine the appropriate handler
 * for a specific request. It plays a crucial role in the request processing pipeline
 * by dynamically selecting the handler that should be used to process and respond
 * to an incoming request.
 *
 * The handler resolver is also an instance of {@see HandlerInterface}, which means
 * that it can be used as a handler itself. This allows the resolver to be used
 * within the request processing pipeline, enabling it to resolve the appropriate
 * handler for the incoming request and delegate the request processing to it.
 */
interface HandlerResolverInterface extends HandlerInterface
{
    /**
     * Resolves and returns the appropriate handler for the specified HTTP request.
     *
     * This method analyzes the request and determines the most suitable handler
     * that can process and generate a response for it.
     *
     * @param Context $context The runtime context for the current request.
     * @param RequestInterface $request The HTTP request for which a handler needs to be resolved.
     *
     * @throws HandlerNotFoundHttpException If no handler can be resolved for the given request.
     * @throws LogicException If the resolved handler is not an instance of {@see HandlerInterface}.
     *
     * @return HandlerInterface The handler capable of processing the request.
     */
    public function resolve(Context $context, RequestInterface $request): HandlerInterface;
}
