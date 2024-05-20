<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Recovery\RecoveryInterface;
use Neu\Component\Http\Runtime\Handler\Resolver\HandlerResolverInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Neu\Component\Http\Runtime\Runtime;
use Neu\Component\Http\Runtime\RuntimeInterface;

/**
 * Factory for creating the runtime.
 *
 * @implements FactoryInterface<Runtime>
 */
final readonly class RuntimeFactory implements FactoryInterface
{
    private int $concurrencyLimit;
    private string $eventDispatcher;
    private string $handlerResolver;
    private string $middlewareQueue;
    private string $recovery;

    public function __construct(?string $eventDispatcher = null, ?string $handlerResolver = null, ?string $middlewareQueue = null, ?string $recovery = null, ?int $concurrencyLimit = null)
    {
        $this->concurrencyLimit = $concurrencyLimit ?? RuntimeInterface::DEFAULT_CONCURRENCY_LIMIT;
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
        $this->handlerResolver = $handlerResolver ?? HandlerResolverInterface::class;
        $this->middlewareQueue = $middlewareQueue ?? MiddlewareQueueInterface::class;
        $this->recovery = $recovery ?? RecoveryInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new Runtime(
            $container->getTyped($this->eventDispatcher, EventDispatcherInterface::class),
            $container->getTyped($this->handlerResolver, HandlerResolverInterface::class),
            $container->getTyped($this->middlewareQueue, MiddlewareQueueInterface::class),
            $container->getTyped($this->recovery, RecoveryInterface::class),
            $this->concurrencyLimit,
        );
    }
}
