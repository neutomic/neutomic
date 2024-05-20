<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Runtime\RuntimeInterface;
use Neu\Component\Http\Server\Server;
use Neu\Component\Http\Server\ServerInfrastructure;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Server} instance.
 *
 * @implements FactoryInterface<Server>
 */
final readonly class ServerFactory implements FactoryInterface
{
    /**
     * The runtime service identifier.
     */
    private string $runtime;

    /**
     * The event dispatcher service identifier.
     */
    private string $eventDispatcher;

    /**
     * The logger service identifier.
     */
    private string $logger;

    /**
     * Create a new {@see ServerFactory} instance.
     *
     * @param string|null $runtime Optional runtime service identifier.
     * @param string|null $eventDispatcher Optional event dispatcher service identifier.
     * @param string|null $logger Optional logger service identifier.
     */
    public function __construct(?string $runtime = null, ?string $eventDispatcher = null, ?string $logger = null)
    {
        $this->runtime = $runtime ?? RuntimeInterface::class;
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Server
    {
        return new Server(
            $container->getTyped(ServerInfrastructure::class, ServerInfrastructure::class),
            $container->getTyped($this->runtime, RuntimeInterface::class),
            $container->getTyped($this->eventDispatcher, EventDispatcherInterface::class),
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
