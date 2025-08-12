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
use Neu\Component\Http\Runtime\RuntimeInterface;
use Neu\Component\Http\Server\Server;
use Neu\Component\Http\Server\ServerInfrastructure;
use Psr\Log\LoggerInterface;
use Override;

/**
 * Factory for creating a {@see Server} instance.
 *
 * @implements FactoryInterface<Server>
 */
final readonly class ServerFactory implements FactoryInterface
{
    /**
     * The runtime service identifier.
     *
     * @var non-empty-string
     */
    private string $runtime;

    /**
     * The event dispatcher service identifier.
     *
     * @var non-empty-string
     */
    private string $eventDispatcher;

    /**
     * The logger service identifier.
     *
     * @var non-empty-string
     */
    private string $logger;

    /**
     * Create a new {@see ServerFactory} instance.
     *
     * @param non-empty-string|null $runtime Optional runtime service identifier.
     * @param non-empty-string|null $eventDispatcher Optional event dispatcher service identifier.
     * @param non-empty-string|null $logger Optional logger service identifier.
     */
    public function __construct(null|string $runtime = null, null|string $eventDispatcher = null, null|string $logger = null)
    {
        $this->runtime = $runtime ?? RuntimeInterface::class;
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcherInterface::class;
        $this->logger = $logger ?? LoggerInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
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
