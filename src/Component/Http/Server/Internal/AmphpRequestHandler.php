<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Internal;

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Represents a request handler that bridges between Amp HTTP server requests and Neu HTTP runtime handler.
 *
 * @internal
 */
final readonly class AmphpRequestHandler implements RequestHandler
{
    /**
     * The Neu runtime handler to handle the converted Neu HTTP server request.
     */
    private HandlerInterface $handler;

    /**
     * Creates a new bridging request handler.
     *
     * @param HandlerInterface $handler The Neu runtime handler to handle the converted Neu HTTP server request.
     */
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Handles an incoming Amp HTTP server request.
     *
     * @param Request $request The incoming Amp HTTP server request.
     *
     * @return Response The response generated by handling the request using the Neu runtime handler.
     */
    public function handleRequest(Request $request): Response
    {
        // Convert the incoming Amp HTTP server request to a Neu HTTP server request,
        // handle it using the Neu runtime handler, and convert the resulting Neu HTTP server response
        // back to an Amp HTTP server response.
        [$context, $request] = MessageConvertor::convertRequest($request);

        return MessageConvertor::convertResponse($this->handler->handle($context, $request));
    }
}