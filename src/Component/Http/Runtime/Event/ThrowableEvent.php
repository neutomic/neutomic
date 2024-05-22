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

use Neu\Component\Http\Exception\HttpExceptionInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

/**
 * Event triggered when a throwable occurs during the processing of an HTTP request.
 *
 * This event allows handlers to provide a custom response for the throwable or to modify the throwable itself.
 */
final readonly class ThrowableEvent implements StoppableEventInterface
{
    /**
     * The context in which the throwable occurred.
     */
    public Context $context;

    /**
     * The HTTP request during which the throwable occurred.
     */
    public RequestInterface $request;

    /**
     * The throwable that was thrown during the request processing.
     */
    public Throwable $throwable;

    /**
     * The response to be sent if handling of the throwable concludes without rethrowing.
     *
     * If null, the throwable will be rethrown.
     */
    public null|ResponseInterface $response;

    /**
     * Constructs a new {@see ThrowableEvent} instance.
     *
     * @param RequestInterface $request The request during which the throwable was thrown.
     * @param Throwable $throwable The throwable that occurred.
     * @param ResponseInterface|null $response Optional initial response to be sent.
     */
    public function __construct(Context $context, RequestInterface $request, Throwable $throwable, null|ResponseInterface $response = null)
    {
        $this->context = $context;
        $this->request = $request;
        $this->throwable = $throwable;
        $this->response = $response;
    }

    /**
     * Returns a new instance of the event with a modified throwable.
     *
     * @param Throwable $throwable The new throwable to replace the current one.
     *
     * @return self A new instance of {@see ThrowableEvent} with the updated throwable.
     */
    public function withError(Throwable $throwable): self
    {
        return new self($this->context, $this->request, $throwable, $this->response);
    }

    /**
     * Returns a new instance of the event with a specified response.
     *
     * This method allows the modification of the response that will be sent following the throwable.
     *
     * If an {@see HttpExceptionInterface} is involved, the response will automatically include the appropriate
     * status code and headers from the exception.
     *
     * @param ResponseInterface $response The new response to be sent.
     *
     * @return self A new instance of {@see ThrowableEvent} with the updated response.
     */
    public function withResponse(ResponseInterface $response): self
    {
        return new self($this->context, $this->request, $this->throwable, $response);
    }

    /**
     * Determines whether the event's propagation has been stopped.
     *
     * Propagation is considered stopped if a response has been set, meaning that the throwable
     * will not be rethrown and the provided response will be sent instead.
     *
     * @return bool True if a response has been set and the event propagation is stopped, otherwise false.
     */
    public function isPropagationStopped(): bool
    {
        return $this->response !== null;
    }
}
