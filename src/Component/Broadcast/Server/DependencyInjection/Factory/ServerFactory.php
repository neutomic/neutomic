<?php

declare(strict_types=1);

namespace Neu\Component\Broadcast\Server\DependencyInjection\Factory;

use Neu\Component\Broadcast\Server\Server;
use Neu\Component\Broadcast\Server\ServerInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * @implements FactoryInterface<ServerInterface>
 */
final class ServerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): ServerInterface
    {
        return new Server(
            $container->getTyped(LoggerInterface::class, LoggerInterface::class),
        );
    }
}
