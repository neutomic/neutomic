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
     *
     * @var non-empty-string
     */
    private string $dispatcher;

    /**
     * The logger service identifier.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * Create a new {@see ClusterWorkerFactory} instance.
     *
     * @param non-empty-string|null $dispatcher Optional event dispatcher service identifier.
     * @param non-empty-string|null $logger Optional logger service identifier.
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
