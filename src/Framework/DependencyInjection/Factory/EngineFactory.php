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

namespace Neu\Framework\DependencyInjection\Factory;

use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Command\Registry\RegistryInterface as ConsoleRegistryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface as EventRegistryInterface;
use Neu\Component\Http\Router\Registry\RegistryInterface as RouterRegistryInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Router\RouteCollector;
use Neu\Component\Http\Server\ClusterInterface;
use Neu\Component\Http\Server\ClusterWorkerInterface;
use Neu\Component\Http\Server\ServerInterface;
use Neu\Framework\Engine;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;

/**
 * Factory for the engine.
 *
 * @implements FactoryInterface<Engine>
 */
final readonly class EngineFactory implements FactoryInterface
{
    /**
     * The console application service id.
     *
     * @var non-empty-string
     */
    private string $application;

    /**
     * The server service id.
     *
     * @var non-empty-string
     */
    private string $server;

    /**
     * The cluster service id.
     *
     * @var non-empty-string
     */
    private string $cluster;

    /**
     * The cluster worker service id.
     *
     * @var non-empty-string
     */
    private string $clusterWorker;

    /**
     * The router registry service id.
     *
     * @var non-empty-string
     */
    private string $routerRegistry;

    /**
     * The route collector service id.
     *
     * @var non-empty-string
     */
    private string $routeCollector;

    /**
     * The middleware queue service id.
     *
     * @var non-empty-string
     */
    private string $middlewareQueue;

    /**
     * The event dispatcher registry service id.
     *
     * @var non-empty-string
     */
    private string $eventDispatcherRegistry;

    /**
     * The command registry service id.
     *
     * @var non-empty-string
     */
    private string $consoleRegistry;

    /**
     * Create a new {@see EngineFactory} instance.
     *
     * @param null|non-empty-string $application The console application service id.
     * @param null|non-empty-string $server The server service id.
     * @param null|non-empty-string $cluster The cluster service id.
     * @param null|non-empty-string $clusterWorker The cluster worker service id.
     * @param null|non-empty-string $routerRegistry The router registry service id.
     * @param null|non-empty-string $routeCollector The route collector service id.
     * @param null|non-empty-string $middlewareQueue The middleware queue service id.
     * @param null|non-empty-string $eventDispatcherRegistry The event dispatcher registry service id.
     * @param null|non-empty-string $consoleRegistry The command registry service id.
     */
    public function __construct(
        null|string $application = null,
        null|string $server = null,
        null|string $cluster = null,
        null|string $clusterWorker = null,
        null|string $routerRegistry = null,
        null|string $routeCollector = null,
        null|string $middlewareQueue = null,
        null|string $eventDispatcherRegistry = null,
        null|string $consoleRegistry = null,
    ) {
        $this->application = $application ?? ApplicationInterface::class;
        $this->server = $server ?? ServerInterface::class;
        $this->cluster = $cluster ?? ClusterInterface::class;
        $this->clusterWorker = $clusterWorker ?? ClusterWorkerInterface::class;
        $this->routerRegistry = $routerRegistry ?? RouterRegistryInterface::class;
        $this->routeCollector = $routeCollector ?? RouteCollector::class;
        $this->middlewareQueue = $middlewareQueue ?? MiddlewareQueueInterface::class;
        $this->eventDispatcherRegistry = $eventDispatcherRegistry ?? EventRegistryInterface::class;
        $this->consoleRegistry = $consoleRegistry ?? ConsoleRegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): Engine
    {
        return new Engine(
            $container,
            $this->application,
            $this->server,
            $this->cluster,
            $this->clusterWorker,
            $this->routerRegistry,
            $this->routeCollector,
            $this->middlewareQueue,
            $this->eventDispatcherRegistry,
            $this->consoleRegistry,
        );
    }
}
