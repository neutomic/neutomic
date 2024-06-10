<?php

declare(strict_types=1);

namespace Neu\Framework\DependencyInjection\Factory\Command\Broadcast\Server;

use Neu\Component\Broadcast\Server\ServerInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Command\Broadcast\Server\StartCommand;

/**
 * @implements FactoryInterface<StartCommand>
 */
final class StartCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): StartCommand
    {
        return new StartCommand(
            $container->getTyped(ServerInterface::class, ServerInterface::class)
        );
    }
}
