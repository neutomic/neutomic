<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\DependencyInjection\Factory\Command;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Server\Command\StartCommand;
use Neu\Component\Http\Server\ServerInterface;

/**
 * @implements FactoryInterface<StartCommand>
 */
final readonly class StartCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): StartCommand
    {
        return new StartCommand(
            $container->getProject()->mode,
            $container->getTyped(ServerInterface::class, ServerInterface::class),
        );
    }
}
