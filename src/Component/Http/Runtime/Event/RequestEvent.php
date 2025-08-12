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

namespace Neu\Component\Http\Runtime\Event;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Represents an event that is triggered when a request is received by the {@see RuntimeInterface}.
 * This event can be used to intercept and modify the request or to provide an early response.
 *
 * This event implements {@see StoppableEventInterface}, allowing the event propagation to be stopped
 * when a response is set, effectively short-circuiting any further request handling.
 */
final readonly class RequestEvent implements StoppableEventInterface
{
    /**
     * @var Context The context in which the request is being handled.
     */
    public Context $context;

    /**
     * @var RequestInterface The HTTP request that triggered the event.
     */
    public RequestInterface $request;

    /**
     * The response to be sent if the event is stopped, this is initially null
     * and can be set to stop the event's propagation.
     */
    public ResponseInterface|null $response;

    /**
     * The handler that will be used to process the request, if any.
     */
    public HandlerInterface|null $handler;

    /**
     * Initializes a new instance of the RequestEvent class.
     *
     * @param RequestInterface $request The HTTP request that triggered the event.
     * @param ResponseInterface|null $response Optional initial response to send back.
     * @param HandlerInterface|null $handler Optional handler to process the request.
     */
    public function __construct(Context $context, RequestInterface $request, null|ResponseInterface $response = null, null|HandlerInterface $handler = null)
    {
        $this->context = $context;
        $this->request = $request;
        $this->response = $response;
        $this->handler = $handler;
    }

    /**
     * Retrieves the request associated with this event.
     *
     * @return RequestInterface The request that was received.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Creates a new event instance with the specified request, preserving the current response if any.
     *
     * @param RequestInterface $request The new request to associate with the event.
     *
     * @return self A new instance of the event with the updated request.
     */
    public function withRequest(RequestInterface $request): self
    {
        return new self($this->context, $request, $this->response);
    }

    /**
     * Creates a new event instance with the specified response, effectively stopping the event propagation.
     *
     * @param ResponseInterface $response The response to associate with the event.
     *
     * @return self A new instance of the event with the response set.
     */
    public function withResponse(ResponseInterface $response): self
    {
        return new self($this->context, $this->request, $response);
    }

    /**
     * Creates a new event instance with the specified handler.
     *
     * @param HandlerInterface $handler The handler to associate with the event.
     *
     * @return self A new instance of the event with the handler set.
     */
    public function withHandler(HandlerInterface $handler): self
    {
        return new self($this->context, $this->request, $this->response, $handler);
    }

    /**
     * Determines whether the event's propagation has been stopped.
     *
     * @return bool True if a response has been set and the event propagation is stopped, otherwise false.
     */
    #[\Override]
    public function isPropagationStopped(): bool
    {
        return $this->response !== null || $this->handler !== null;
    }
}
