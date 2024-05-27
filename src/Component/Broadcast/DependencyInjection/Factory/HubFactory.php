<?php

namespace Neu\Component\Broadcast\DependencyInjection\Factory;

use Neu\Component\Broadcast\Hub;
use Neu\Component\Broadcast\Transport\TransportInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * The factory for creating broadcast hubs.
 *
 * @implements FactoryInterface<Hub>
 */
final readonly class HubFactory implements FactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $transport;

    /**
     * @param null|non-empty-string $transport The service identifier of the transport to use, defaults to {@see TransportInterface}.
     */
    public function __construct(null|string $transport = null)
    {
        $this->transport = $transport ?? TransportInterface::class;
    }
    public function __invoke(ContainerInterface $container): object
    {
        $transport = $container->getTyped($this->transport, TransportInterface::class);

        return new Hub($transport);
    }
}
