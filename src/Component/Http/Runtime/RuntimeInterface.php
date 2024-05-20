<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

/**
 * Defines the interface for the HTTP runtime.
 *
 * This interface encapsulates the handling of HTTP requests and integrates event dispatching
 * capabilities to facilitate the management and observation of the request/response lifecycle.
 * It dispatches various events including request received, response ready, exception occurred,
 * and final termination events.
 */
interface RuntimeInterface extends HandlerInterface
{
    public const int DEFAULT_CONCURRENCY_LIMIT = 100;

    /**
     * Retrieves the maximum number of concurrent requests the runtime can handle.
     *
     * @return int The maximum number of concurrent requests allowed.
     */
    public function getConcurrencyLimit(): int;

    /**
     * Retrieves the current number of active requests being processed by the runtime.
     *
     * @return int The number of active requests.
     */
    public function getActiveRequestsCount(): int;

    /**
     * Retrieves the current number of pending requests waiting to be processed by the runtime.
     *
     * @return int The number of pending requests.
     */
    public function getPendingRequestsCount(): int;

    /**
     * Handles the given HTTP request and produces a response.
     *
     * This method is responsible for processing an incoming request and generating the
     * appropriate response based on the application logic and available resources.
     *
     * If the concurrency limit is reached, this method will wait until a spot becomes available
     * before handling the request, ensuring that system resources are managed effectively and
     * preventing overload.
     *
     * During its execution, it dispatches events related to the request handling,
     * response preparation, and exception handling, enabling the application to react
     * dynamically at different stages of the request processing.
     *
     * @param RequestInterface $request The HTTP request to handle.
     *
     * @return ResponseInterface The response to the given request.
     *
     * @see Event\RequestEvent for the request handling event.
     * @see Event\ResponseEvent for the response preparation event.
     * @see Event\ThrowableEvent for the exception handling event.
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface;

    /**
     * Terminates the request/response cycle.
     *
     * This method is called after the response has been sent to the client, providing an
     * opportunity to perform cleanup, logging, or other shutdown procedures.
     *
     * It dispatches a termination event, signaling that the request and response lifecycle
     * has fully concluded.
     *
     * @param RequestInterface $request The request that was processed.
     * @param ResponseInterface $response The response that was sent to the client.
     *
     * @see Event\TerminateEvent for the final termination event.
     */
    public function terminate(Context $context, RequestInterface $request, ResponseInterface $response): void;
}
