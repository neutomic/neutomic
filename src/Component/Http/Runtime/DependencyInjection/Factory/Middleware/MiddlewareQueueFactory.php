<?php

declare(strict_types=1);

namespace Neu\Component\Http\Runtime\DependencyInjection\Factory\Middleware;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueue;

/**
 * Factory for creating a {@see MiddlewareQueue} instance.
 *
 * @implements FactoryInterface<MiddlewareQueue>
 */
final readonly class MiddlewareQueueFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): MiddlewareQueue
    {
        return new MiddlewareQueue();
    }
}
