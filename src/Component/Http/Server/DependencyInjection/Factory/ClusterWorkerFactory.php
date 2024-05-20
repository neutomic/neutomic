<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Server\ClusterWorker;
use Neu\Component\Http\Server\ServerInterface;
use Psr\Log\LoggerInterface;

/**
 * @implements FactoryInterface<ClusterWorker>
 */
final readonly class ClusterWorkerFactory implements FactoryInterface
{
    /**
     * The event dispatcher service identifier.
     */
    private string $dispatcher;

    /**
     * The logger service identifier.
     */
    private string $logger;

    /**
     * Create a new {@see ClusterWorkerFactory} instance.
     *
     * @param string|null $dispatcher Optional event dispatcher service identifier.
     * @param string|null $logger Optional logger service identifier.
     */
    public function __construct(null|string $dispatcher = null, null|string $logger = null)
    {
        $this->dispatcher = $dispatcher ?? EventDispatcherInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): ClusterWorker
    {
        return new ClusterWorker(
            $container->getTyped(ServerInterface::class, ServerInterface::class),
            $container->getTyped($this->dispatcher, EventDispatcherInterface::class),
            $container->getTyped($this->logger, LoggerInterface::class),
        );
    }
}
