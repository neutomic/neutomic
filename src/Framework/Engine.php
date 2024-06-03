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

use Amp\Cluster\Cluster;
use Neu\Component\Console\ApplicationInterface;
use Neu\Component\Console\Terminal;
use Neu\Component\Console\Command\Registry\RegistryInterface as ConsoleRegistryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface as EventRegistryInterface;
use Neu\Component\Http\Router\Registry\RegistryInterface as RouterRegistryInterface;
use Neu\Component\Http\Server\ClusterWorkerInterface;
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
     * @param ApplicationInterface $application The application to run.
     * @param ClusterWorkerInterface $clusterWorker The cluster worker to run.
     * @param ConsoleRegistryInterface $consoleRegistry The command registry.
     * @param RouterRegistryInterface $routerRegistry The router registry.
     * @param EventRegistryInterface $eventDispatcherRegistry The event registry.
     * @param MiddlewareQueueInterface $middlewareQueue The middleware queue.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ApplicationInterface $application,
        private readonly ClusterWorkerInterface $clusterWorker,
        private readonly RouterRegistryInterface $routerRegistry,
        private readonly MiddlewareQueueInterface $middlewareQueue,
        private readonly EventRegistryInterface $eventDispatcherRegistry,
        private readonly ConsoleRegistryInterface $consoleRegistry,
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
    public function run(): void
    {
        $project = $this->container->getProject();
        if ($project->debug) {
            Env\set_var('AMP_DEBUG', '1');
            Env\set_var('REVOLT_DRIVER_DEBUG_TRACE', '1');
        }

        foreach ($this->plugins as $plugin) {
            try {
                $plugin->boot($this->container);
                $plugin->route($this->container, $this->routerRegistry);
                $plugin->enqueue($this->container, $this->middlewareQueue);
                $plugin->listen($this->container, $this->eventDispatcherRegistry);
                $plugin->command($this->container, $this->consoleRegistry);
            } catch (Throwable $e) {
                throw new Exception\RuntimeException('Failed to start the engine: plugin "' . $plugin::class . '" failed to initialize.', 0, $e);
            }
        }

        try {
            if (Cluster::isWorker()) {
                Env\set_var('NONINTERACTIVE', '1');

                // we are in a worker process, start the worker.
                $this->clusterWorker->start();
                Cluster::awaitTermination();
                $this->clusterWorker->stop();
            } else {
                Env\set_var('COLUMNS', (string) Terminal::getWidth());
                Env\set_var('LINES', (string) Terminal::getHeight());
                Env\set_var('CLICOLORS', Terminal::hasColorSupport() ? '1' : '0');
                Env\set_var('AMP_LOG_COLOR', Terminal::hasColorSupport() ? '1' : '0');

                // we are in the main process, run the application.
                $this->application->run();
            }
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to run the engine: ' . $e->getMessage(), 0, $e);
        }

        foreach ($this->plugins as $plugin) {
            try {
                $plugin->shutdown($this->container);
            } catch (Throwable $e) {
                throw new Exception\RuntimeException('Failed to stop the engine: plugin "' . $plugin::class . '" failed to shutdown.', 0, $e);
            }
        }
    }
}
