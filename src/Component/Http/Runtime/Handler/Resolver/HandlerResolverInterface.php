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
use Neu\Component\Http\Runtime\Exception\HandlerNotFoundHttpException;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Interface for resolving a handler based on the given HTTP request.
 *
 * This interface is used within the runtime to determine the appropriate handler
 * for a specific request. It plays a crucial role in the request processing pipeline
 * by dynamically selecting the handler that should be used to process and respond
 * to an incoming request.
 */
interface HandlerResolverInterface
{
    /**
     * Resolves and returns the appropriate handler for the specified HTTP request.
     *
     * This method analyzes the request and determines the most suitable handler
     * that can process and generate a response for it. This typically involves
     * looking up handler mappings based on the request's attributes such as the
     * URL path, HTTP method, headers, or other criteria specific to the application.
     *
     * @param RequestInterface $request The HTTP request for which a handler needs to be resolved.
     *
     * @throws HandlerNotFoundHttpException If no handler can be resolved for the given request.
     * @throws LogicException If the resolved handler is not an instance of {@see HandlerInterface}.
     *
     * @return HandlerInterface The handler capable of processing the request.
     */
    public function resolve(RequestInterface $request): HandlerInterface;
}
