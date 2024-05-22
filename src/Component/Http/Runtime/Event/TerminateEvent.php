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

/**
 * Event triggered after a response has been sent to the client.
 *
 * This event provides access to the request and response objects for logging,
 * auditing, or other post-response activities. It does not allow modification
 * of the response since it is dispatched after the response has already been sent.
 */
final readonly class TerminateEvent
{
    /**
     * The context in which the request was handled.
     */
    public Context $context;

    /**
     * The request that was received and processed.
     */
    public RequestInterface $request;

    /**
     * The response that was sent to the client.
     */
    public ResponseInterface $response;

    /**
     * Initializes a new instance of the TerminateEvent class.
     *
     * @param Context $context The context in which the request was handled.
     * @param RequestInterface $request The request that was handled.
     * @param ResponseInterface $response The response that was sent to the client.
     */
    public function __construct(Context $context, RequestInterface $request, ResponseInterface $response)
    {
        $this->context = $context;
        $this->request = $request;
        $this->response = $response;
    }
}
