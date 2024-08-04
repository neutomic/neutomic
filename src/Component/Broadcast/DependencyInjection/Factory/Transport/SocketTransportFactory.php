<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Neu\Component\Broadcast\Exception\RuntimeException;
use Neu\Component\Broadcast\Transport\SocketTransport;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * @implements FactoryInterface<SocketTransport>
 */
final readonly class SocketTransportFactory implements FactoryInterface
{
    /**
     * @param non-empty-string $address
     */
    public function __construct(
        private string $address
    )
    {
    }

    /**
     * @throws RuntimeException if fails to connect to the server
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new SocketTransport($this->address);
    }
}
