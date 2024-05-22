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

namespace Neu\Component\Http\Server;

use Amp\Cluster\Cluster as AmpCluster;
use Amp\Cluster\ClusterWatcher;
use Neu\Component\Http\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Throwable;

use function Amp\Cluster\countCpuCores;

/**
 * A cluster that manages multiple worker processes.
 *
 * @psalm-suppress MissingThrowsDocblock
 */
final class Cluster implements ClusterInterface
{
    /**
     * The path to the entrypoint file for executes the {@see ClusterWorkerInterface} instance.
     */
    private string $entrypoint;

    /**
     * The logger instance for logging cluster activities.
     */
    private LoggerInterface $logger;

    /**
     * The cluster watcher instance to manage worker processes.
     */
    private null|ClusterWatcher $watcher = null;

    /**
     * The Number of worker processes to be managed by the cluster.
     */
    private int $workerCount;

    /**
     * Create a new {@see Cluster} instance.
     *
     * @param string $entrypoint Path to the entrypoint file for executes the {@see ClusterWorkerInterface} instance.
     * @param LoggerInterface $logger Logger instance for logging cluster activities.
     * @param int|null $workerCount Optional number of workers to start. If null, the number of CPU cores will be used.
     */
    public function __construct(string $entrypoint, LoggerInterface $logger, null|int $workerCount = null)
    {
        $this->entrypoint = $entrypoint;
        $this->logger = $logger;
        $this->workerCount = $workerCount ?? (countCpuCores() * 2);
    }

    /**
     * @inheritDoc
     */
    public function start(null|int $workers = null): void
    {
        if (null !== $this->watcher) {
            return;
        }

        if (AmpCluster::isWorker()) {
            throw new RuntimeException('Cluster cannot be started from within a worker process.');
        }

        $this->watcher = $watcher = new ClusterWatcher([
            __DIR__ . '/Internal/cluster-worker.php',
            $this->entrypoint,
        ], $this->logger);

        $workers = $workers ?? $this->workerCount;

        $this->logger->info('Starting cluster with {workers} workers...', ['workers' => $workers]);

        $watcher->start($workers);

        $this->logger->info('Cluster started with {workers} workers.', ['workers' => $workers]);
    }

    /**
     * @inheritDoc
     */
    public function restart(): void
    {
        if (null === $this->watcher) {
            $this->start();

            return;
        }

        $watcher = $this->watcher;

        $this->logger->info('Restarting cluster...');

        $watcher->restart();

        $this->logger->info('Cluster restarted.');
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        if (null === $this->watcher) {
            return;
        }

        $watcher = $this->watcher;

        $this->logger->info('Stopping cluster...');

        try {
            $watcher->stop();

            $this->logger->info('Cluster stopped successfully.');
        } catch (Throwable $exception) {
            $this->logger->error('Error while stopping cluster.', [
                'exception' => $exception,
            ]);

            throw $exception;
        } finally {
            $this->watcher = null;
        }
    }
}
