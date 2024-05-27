<?php

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Neu\Component\Broadcast\Transport\MemoryTransport;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<MemoryTransport>
 */
final readonly class MemoryTransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new MemoryTransport();
    }
}
