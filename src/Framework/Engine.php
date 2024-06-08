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

namespace Neu\Framework;

use Amp\Cluster\Cluster as AmpCluster;
use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Terminal;
use Neu\Component\Console\Command\Registry\RegistryInterface as ConsoleRegistryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface as EventRegistryInterface;
use Neu\Component\Http\Router\Registry\RegistryInterface as RouterRegistryInterface;
use Neu\Component\Http\Router\RouteCollector;
use Neu\Component\Http\Server\Cluster;
use Neu\Component\Http\Server\ClusterWorkerInterface;
use Neu\Component\Http\Server\ServerInterface;
use Neu\Framework\Plugin\PluginInterface;
use Psl\Env;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;
use Throwable;

/**
 * An engine that initializes the project, manages plugins, and runs the application.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class Engine implements EngineInterface
{
    /**
     * The list of plugins to run.
     *
     * @var list<PluginInterface>
     */
    private array $plugins = [];

    /**
     * Create a new {@see Engine} instance.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param non-empty-string $applicationServiceId The console application service id.
     * @param non-empty-string $serverServiceId The server service id.
     * @param non-empty-string $clusterServiceId The cluster service id.
     * @param non-empty-string $clusterWorkerServiceId The cluster worker service id.
     * @param non-empty-string $routerRegistryServiceId The router registry service id.
     * @param non-empty-string $routeCollectorServiceId The route collector service id.
     * @param non-empty-string $middlewareQueueServiceId The middleware queue service id.
     * @param non-empty-string $eventDispatcherRegistryServiceId The event registry service id.
     * @param non-empty-string $consoleRegistryServiceId The command registry service id.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $applicationServiceId,
        private readonly string $serverServiceId,
        private readonly string $clusterServiceId,
        private readonly string $clusterWorkerServiceId,
        private readonly string $routerRegistryServiceId,
        private readonly string $routeCollectorServiceId,
        private readonly string $middlewareQueueServiceId,
        private readonly string $eventDispatcherRegistryServiceId,
        private readonly string $consoleRegistryServiceId,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function inject(PluginInterface $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function run(Mode $mode = Mode::Application): void
    {
        $project = $this->container->getProject();

        Env\set_var('AMP_DEBUG', $project->debug ? '1' : '0');
        Env\set_var('COLUMNS', (string) Terminal::getWidth());
        Env\set_var('LINES', (string) Terminal::getHeight());
        Env\set_var('AMP_LOG_COLOR', Terminal::hasColorSupport() ? '1' : 'off');
        Env\set_var('CLICOLORS', Terminal::hasColorSupport() ? '1' : '0');

        $this->setupPlugins();

        match ($mode) {
            Mode::ConsoleOnly => $this->runConsoleOnly(),
            Mode::HttpServer => $this->runHttpServer(),
            Mode::HttpCluster => $this->runHttpCluster(),
            Mode::Application => $this->runApplication(),
        };

        $this->tearDownPlugins();
    }

    /**
     * Run the console application.
     *
     * @throws Exception\RuntimeException If an error occurs while running the engine.
     */
    private function runConsoleOnly(): void
    {
        try {
            $application = $this->container->getTyped($this->applicationServiceId, ApplicationInterface::class);
            $application->run();
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to run the engine in console mode: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Run the engine in server mode.
     *
     * @throws Exception\RuntimeException If an error occurs while running the engine.
     */
    private function runHttpServer(): void
    {
        try {
            $server = $this->container->getTyped($this->serverServiceId, ServerInterface::class);
            $server->start();
            AmpCluster::awaitTermination();
            $server->stop();
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to run the engine in server mode: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Run the engine in cluster mode.
     *
     * @throws Exception\RuntimeException If an error occurs while running the engine.
     */
    private function runHttpCluster(): void
    {
        try {
            if (AmpCluster::isWorker()) {
                $this->runClusterWorker();
            } else {
                $this->runClusterWatcher();
            }
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to run the engine in cluster mode: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Run the engine in application mode.
     *
     * @throws Exception\RuntimeException If an error occurs while running the engine.
     */
    private function runApplication(): void
    {
        try {
            if (AmpCluster::isWorker()) {
                $this->runClusterWorker();
            } else {
                $this->runConsoleOnly();
            }
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to run the engine in application mode: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Run the cluster watcher.
     *
     * @throws Throwable If an error occurs while running the engine.
     */
    private function runClusterWatcher(): void
    {
        $cluster = $this->container->getTyped($this->clusterServiceId, Cluster::class);
        $cluster->start();
        AmpCluster::awaitTermination();
        $cluster->stop();
    }

    /**
     * Run the cluster worker.
     *
     * @throws Throwable If an error occurs while running the engine.
     */
    private function runClusterWorker(): void
    {
        Env\set_var('NONINTERACTIVE', '1');

        $worker = $this->container->getTyped($this->clusterWorkerServiceId, ClusterWorkerInterface::class);
        $worker->start();
        AmpCluster::awaitTermination();
        $worker->stop();
    }


    /**
     * Set up the plugins.
     *
     * @throws Exception\RuntimeException
     */
    private function setupPlugins(): void
    {
        $routes = $this->container->getTyped($this->routerRegistryServiceId, RouterRegistryInterface::class);
        $collector = $this->container->getTyped($this->routeCollectorServiceId, RouteCollector::class);
        $middleware = $this->container->getTyped($this->middlewareQueueServiceId, MiddlewareQueueInterface::class);
        $events = $this->container->getTyped($this->eventDispatcherRegistryServiceId, EventRegistryInterface::class);
        $commands = $this->container->getTyped($this->consoleRegistryServiceId, ConsoleRegistryInterface::class);

        foreach ($this->plugins as $plugin) {
            try {
                $plugin->boot($this->container);
                $plugin->route($this->container, $routes, $collector);
                $plugin->enqueue($this->container, $middleware);
                $plugin->listen($this->container, $events);
                $plugin->command($this->container, $commands);
            } catch (Throwable $e) {
                throw new Exception\RuntimeException(
                    message: 'Failed to start the engine: plugin "' . $plugin::class . '" failed to initialize.',
                    previous: $e,
                );
            }
        }
    }

    /**
     * Tear down the plugins.
     *
     * @throws Exception\RuntimeException
     */
    private function tearDownPlugins(): void
    {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->shutdown($this->container);
            } catch (Throwable $e) {
                throw new Exception\RuntimeException(
                    message: 'Failed to stop the engine: plugin "' . $plugin::class . '" failed to shutdown.',
                    previous: $e,
                );
            }
        }
    }
}
