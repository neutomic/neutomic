<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Neu\Component\Broadcast\Transport\LazyTransport;
use Neu\Component\Broadcast\Transport\TransportInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<LazyTransport>
 */
final class LazyTransportFactory implements FactoryInterface
{
    /**
     * @param FactoryInterface<TransportInterface> $innerFactory
     */
    public function __construct(private FactoryInterface $innerFactory)
    {
    }

    public function __invoke(ContainerInterface $container): object
    {
        return new LazyTransport($container, $this->innerFactory);
    }
}
