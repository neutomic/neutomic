<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\DependencyInjection\Factory\Transport;

use Amp\Socket\ResourceServerSocketFactory;
use Neu\Component\Broadcast\Address\UnixAddress;
use Neu\Component\Broadcast\Transport\SocketTransport;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use function Amp\Socket\connect;

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

    public function __invoke(ContainerInterface $container): object
    {
        return new SocketTransport($this->address);
    }
}
