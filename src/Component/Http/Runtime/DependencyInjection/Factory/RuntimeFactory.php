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
    /**
     * @var non-empty-string
     */
    private string $eventDispatcher;

    /**
     * @var non-empty-string
     */
    private string $handlerResolver;

    /**
     * @var non-empty-string
     */
    private string $middlewareQueue;

    /**
     * @var non-empty-string
     */
    private string $recovery;

    /**
     * @var positive-int
     */
    private int $concurrencyLimit;

    /**
     * @param non-empty-string|null $eventDispatcher
     * @param non-empty-string|null $handlerResolver
     * @param non-empty-string|null $middlewareQueue
     * @param non-empty-string|null $recovery
     * @param positive-int|null $concurrencyLimit
     */
    public function __construct(null|string $eventDispatcher = null, null|string $handlerResolver = null, null|string $middlewareQueue = null, null|string $recovery = null, null|int $concurrencyLimit = null)
    {
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
        $this->handlerResolver = $handlerResolver ?? HandlerResolverInterface::class;
        $this->middlewareQueue = $middlewareQueue ?? MiddlewareQueueInterface::class;
        $this->recovery = $recovery ?? RecoveryInterface::class;
        $this->concurrencyLimit = $concurrencyLimit ?? RuntimeInterface::DEFAULT_CONCURRENCY_LIMIT;
    }

    /**
     * @inheritDoc
     */
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
