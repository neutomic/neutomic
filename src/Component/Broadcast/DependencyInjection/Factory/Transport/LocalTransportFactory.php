<?php

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Neu\Component\Broadcast\Transport\LocalTransport;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<LocalTransport>
 */
final readonly class LocalTransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new LocalTransport();
    }
}
