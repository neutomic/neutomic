<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Http\Server\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Server\Cluster;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Cluster} instance.
 *
 * @implements FactoryInterface<Cluster>
 */
final readonly class ClusterFactory implements FactoryInterface
{
    /**
     * The event dispatcher service to be used by the cluster.
     *
     * @var non-empty-string
     */
    private string $eventDispatcher;

    /**
     * The logger service to be used by the cluster.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * The number of worker processes to be managed by the cluster.
     *
     * @var positive-int|null
     */
    private null|int $workers;

    /**
     * Create a new {@see ClusterFactory} instance.
     *
     * @param non-empty-string|null $eventDispatcher The event dispatcher service to be used by the cluster.
     * @param non-empty-string|null $logger The logger service to be used by the cluster.
     * @param positive-int|null $workers The number of worker processes to be managed by the cluster.
     */
    public function __construct(null|string $eventDispatcher = null, null|string $logger = null, null|int $workers = null)
    {
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
        $this->workers = $workers;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container): Cluster
    {
        $entrypoint = $container->getProject()->entrypoint;
        $eventDispatcher = $container->getTyped($this->eventDispatcher, EventDispatcherInterface::class);
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Cluster($entrypoint, $eventDispatcher, $logger, $this->workers);
    }
}
