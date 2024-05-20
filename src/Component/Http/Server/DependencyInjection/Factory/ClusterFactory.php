<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Server\Cluster;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Cluster} instance.
 *
 * @implements FactoryInterface<Cluster>
 */
final readonly class ClusterFactory implements FactoryInterface
{
    private string $logger;
    private ?int $workers;

    public function __construct(?string $logger = null, ?int $workers = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->workers = $workers;
    }

    public function __invoke(ContainerInterface $container): Cluster
    {
        $entrypoint = $container->getProject()->entrypoint;
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Cluster($entrypoint, $logger, $this->workers);
    }
}
