<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime;

use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Exception\HttpExceptionInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Recovery\RecoveryInterface;
use Neu\Component\Http\Runtime\Event\RequestEvent;
use Neu\Component\Http\Runtime\Event\ResponseEvent;
use Neu\Component\Http\Runtime\Event\TerminateEvent;
use Neu\Component\Http\Runtime\Event\ThrowableEvent;
use Neu\Component\Http\Runtime\Handler\ClosureHandler;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolverInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Psl\Async\Semaphore;
use Throwable;

/**
 * The Runtime class implements the RuntimeInterface to manage the HTTP request lifecycle,
 * integrating event dispatching, exception handling, and middleware execution.
 *
 * This class serves as the central execution point for handling HTTP requests, resolving
 * handlers, processing requests through middleware, and handling responses and exceptions.
 * It utilizes a semaphore to manage concurrency, ensuring that the system does not exceed
 * the defined limit of concurrent request processing.
 */
final class Runtime implements RuntimeInterface
{
    /**
     * The dispatcher used to trigger events throughout the request handling process.
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The queue of middleware that requests are processed through.
     */
    private MiddlewareQueueInterface $queue;

    /**
     * The resolver used to determine the appropriate handler for a given request.
     */
    private HandlerResolverInterface $resolver;

    /**
     * The recovery strategy to handle exceptions that occur during request processing.
     */
    private RecoveryInterface $recovery;

    /**
     * A semaphore to limit the number of concurrent requests handled by the runtime.
     *
     * @var Semaphore<RequestInterface, ResponseInterface>
     */
    private Semaphore $semaphore;

    /**
     * Constructs a new Runtime instance with specified components for handling HTTP requests.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher to trigger various lifecycle events.
     * @param HandlerResolverInterface $resolver The resolver to find appropriate handlers for requests.
     * @param MiddlewareQueueInterface $queue The middleware queue to process requests.
     * @param int $concurrencyLimit The maximum number of concurrent requests the runtime can handle.
     */
    public function __construct(EventDispatcherInterface $dispatcher, HandlerResolverInterface $resolver, MiddlewareQueueInterface $queue, RecoveryInterface $recovery, int $concurrencyLimit = self::DEFAULT_CONCURRENCY_LIMIT)
    {
        $this->dispatcher = $dispatcher;
        $this->queue = $queue;
        $this->resolver = $resolver;
        $this->recovery = $recovery;
        $this->semaphore = new Semaphore($concurrencyLimit, $this->handleRequest(...));
    }

    /**
     * @inheritDoc
     */
    public function getConcurrencyLimit(): int
    {
        return $this->semaphore->getConcurrencyLimit();
    }

    /**
     * @inheritDoc
     */
    public function getActiveRequestsCount(): int
    {
        return $this->semaphore->getIngoingOperations();
    }

    /**
     * @inheritDoc
     */
    public function getPendingRequestsCount(): int
    {
        return $this->semaphore->getPendingOperations();
    }

    /**
     * @inheritDoc
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return $this->semaphore->waitFor([$context, $request]);
    }

    /**
     * @inheritDoc
     */
    public function terminate(Context $context, RequestInterface $request, ResponseInterface $response): void
    {
        $this->dispatcher->dispatch(new TerminateEvent($context, $request, $response));
    }

    /**
     * Handles an incoming HTTP request by processing it through middleware and potentially resolving it to a handler.
     *
     * This method is the core of the request handling process, where the request is initially processed through
     * a middleware stack. After middleware processing, the request may either be resolved directly to a response
     * or further processed by a handler which is resolved using the HandlerResolverInterface.
     *
     * The process involves dispatching several events:
     * - `RequestEvent`: Triggered before the request is processed by a handler.
     * - `HandlerEvent`: Triggered when a handler is about to process the request.
     * - Any exceptions that occur during handling are managed by dispatching `ExceptionEvent`.
     *
     * The method also ensures that any response, either from the handler or as a result of exception handling,
     * is passed through a response event for potential modifications before being returned.
     *
     * @param array{Context, RequestInterface} $input The input array containing the context and request to process.
     *
     * @throws Throwable If an unhandled throwable occurs during the request processing, it is re-thrown after dispatching an {@see ThrowableEvent}.
     *
     * @return ResponseInterface The HTTP response after processing the request and any applicable middleware.
     */
    private function handleRequest(array $input): ResponseInterface
    {
        [$context, $request] = $input;

        try {
            $event = $this->dispatcher->dispatch(new RequestEvent($context, $request));
            $request = $event->request;
            $handler  = $event->handler;
            $response = $event->response;
            if (null === $response) {
                if (null === $handler) {
                    $handler = new ClosureHandler(function (Context $context, RequestInterface $request): ResponseInterface {
                        return $this->resolver->resolve($request)->handle($context, $request);
                    });
                }

                $response = $this->queue->wrap($handler)->handle($context, $request);
            }
        } catch (Throwable $throwable) {
            $response = $this->handleThrowable($context, $request, $throwable);
        }

        return $this->handleResponse($context, $request, $response);
    }

    /**
     * Dispatches a {@see ResponseEvent} for the given request and response.
     *
     * This method is responsible for allowing modifications to the response
     * just before it is returned from the runtime. It may be used for last-minute
     * adjustments or logging purposes.
     *
     * @param RequestInterface $request The HTTP request associated with the response.
     * @param ResponseInterface $response The HTTP response to potentially modify.
     *
     * @return ResponseInterface The potentially modified HTTP response.
     */
    private function handleResponse(Context $context, RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $event = $this->dispatcher->dispatch(new ResponseEvent($context, $request, $response));

        return $event->response;
    }

    /**
     * Handles errors that occur during request processing by dispatching an {@see ThrowableEvent}.
     *
     * If a response is provided by the {@see ThrowableEvent}, it returns this response. Otherwise, it re-throws
     * the exception.
     *
     * If the error is an instance of {@see HttpExceptionInterface}, the response will be modified
     * to include the appropriate status code and headers from the exception.
     *
     * @param Context $context The context during which the error occurred.
     * @param RequestInterface $request The request during which the error occurred.
     * @param Throwable $error The error that needs handling.
     *
     * @throws Throwable Re-throws the error if no response is provided by the {@see ThrowableEvent}.
     *
     * @return ResponseInterface The response derived from handling the error.
     */
    private function handleThrowable(Context $context, RequestInterface $request, Throwable $throwable): ResponseInterface
    {
        $event = $this->dispatcher->dispatch(new ThrowableEvent($context, $request, $throwable));
        $throwable = $event->throwable;
        $response = $event->response;
        if (null === $response) {
            $response = $this->recovery->recover($context, $request, $throwable);
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $response = $response->withStatus($throwable->getStatusCode());
            foreach ($throwable->getHeaders() as $name => $values) {
                $response = $response->withHeader($name, $values);
            }
        }

        return $response;
    }
}
