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

namespace Neu\Tests\Component\Http\Runtime\Middleware;

use Amp\Http\Server\Driver\Client;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueue;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class MiddlewareQueueTest extends TestCase
{
    private Context $context;

    protected function setUp(): void
    {
        $this->context = new Context(
            workerId: null,
            client: $this->createMock(Client::class),
            sendInformationalResponse: static fn () => null,
        );
    }

    /**
     * @throws Exception
     */
    public function testEmptyQueue(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);

        $handler->expects(static::once())->method('handle')->with($this->context, $request)->willReturn($response);

        $queue = new MiddlewareQueue();

        static::assertSame($response, $queue->wrap($handler)->handle($this->context, $request));
    }

    /**
     * @throws Exception
     */
    public function testQueue(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::never())->method('handle');

        $middlewareOne->expects(static::once())->method('process')->willReturn($response);
        $middlewareTwo->expects(static::once())->method('process')->willReturnCallback(
            static fn (Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface =>
                $next->handle($context, $request)
        );

        $queue = new MiddlewareQueue();
        $queue->enqueue($middlewareOne);
        $queue->enqueue($middlewareTwo);

        static::assertSame($response, $queue->wrap($handler)->handle($this->context, $request));
    }

    /**
     * @throws Exception
     */
    public function testQueueCanBeReused(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::never())->method('handle');
        $middlewareOne->expects(static::exactly(2))->method('process')->willReturn($response);
        $middlewareTwo->expects(static::exactly(2))->method('process')->willReturnCallback(
            static fn (Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface =>
            $next->handle($context, $request)
        );

        $queue = new MiddlewareQueue();
        $queue->enqueue($middlewareOne);
        $queue->enqueue($middlewareTwo);

        static::assertSame($response, $queue->wrap($handler)->handle($this->context, $request));
        static::assertSame($response, $queue->wrap($handler)->handle($this->context, $request));
    }

    /**
     * @throws Exception
     */
    public function testWrappedHandlerCanBeReused(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::never())->method('handle');
        $middlewareOne->expects(static::exactly(2))->method('process')->willReturn($response);
        $middlewareTwo->expects(static::exactly(2))->method('process')->willReturnCallback(
            static fn (Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface =>
            $next->handle($context, $request)
        );

        $queue = new MiddlewareQueue();
        $queue->enqueue($middlewareOne);
        $queue->enqueue($middlewareTwo);

        $handler = $queue->wrap($handler);

        static::assertSame($response, $handler->handle($this->context, $request));
        static::assertSame($response, $handler->handle($this->context, $request));
    }

    /**
     * @throws Exception
     */
    public function testHandlerIsCalledIfAllMiddlewareDelegate(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(HandlerInterface::class);
        $middlewareOne = $this->createMock(MiddlewareInterface::class);
        $middlewareTwo = $this->createMock(MiddlewareInterface::class);

        $handler->expects(static::once())->method('handle')->with($this->context, $request)->willReturn($response);

        $middlewareTwo->expects(static::once())->method('process')->willReturnCallback(
            static fn (Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface =>
                $next->handle($context, $request)
        );

        $middlewareOne->expects(static::once())->method('process')->willReturnCallback(
            static fn (Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface =>
                $next->handle($context, $request)
        );

        $queue = new MiddlewareQueue();
        $queue->enqueue($middlewareOne);
        $queue->enqueue($middlewareTwo);

        static::assertSame($response, $queue->wrap($handler)->handle($this->context, $request));
    }
}
