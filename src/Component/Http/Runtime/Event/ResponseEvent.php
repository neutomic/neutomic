<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\Event;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;

/**
 * Event triggered when a response is ready to be sent back to the client.
 *
 * This event can be used to modify the response after it has been created.
 * It does not allow for modifying the request, which is considered to have been fully handled at this point.
 */
final readonly class ResponseEvent
{
    /**
     * The context in which the response is being generated.
     */
    public Context $context;

    /**
     * The original request that was received and processed to generate the response.
     */
    public RequestInterface $request;

    /**
     * The response that has been generated and is ready to be sent.
     */
    public ResponseInterface $response;

    /**
     * Initializes a new instance of the ResponseEvent class.
     *
     * @param RequestInterface $request The request that led to the generation of this response.
     * @param ResponseInterface $response The initial response that has been created.
     */
    public function __construct(Context $context, RequestInterface $request, ResponseInterface $response)
    {
        $this->context = $context;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Creates a new event instance with an updated response, allowing for modifications
     * to be made to the response before it is sent.
     *
     * @param ResponseInterface $response The new response to replace the current one.
     *
     * @return self A new instance of the event with the updated response.
     */
    public function withResponse(ResponseInterface $response): self
    {
        return new self($this->context, $this->request, $response);
    }
}
