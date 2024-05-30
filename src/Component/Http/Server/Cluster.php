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
use Amp\Cluster\ClusterException;
use Amp\Cluster\ClusterWatcher;
use Amp\Cluster\ClusterWorkerMessage;
use Amp\Parallel\Context\ContextException;
use Amp\Sync\Channel;
use Amp\Sync\Mutex;
use Amp\Sync\Parcel;
use Amp\Sync\PosixSemaphore;
use Amp\Sync\Semaphore;
use Amp\Sync\SemaphoreMutex;
use Amp\Sync\SharedMemoryParcel;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Server\Event\ClusterRestartedEvent;
use Neu\Component\Http\Server\Event\ClusterStartedEvent;
use Neu\Component\Http\Server\Event\ClusterStoppedEvent;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Throwable;

use function Amp\Cluster\countCpuCores;
use function count;
use function pack;

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
     * The event dispatcher instance for dispatching cluster events.
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The logger instance for logging cluster activities.
     */
    private LoggerInterface $logger;

    /**
     * The cluster watcher instance to manage worker processes.
     *
     * @var null|ClusterWatcher<mixed, mixed>
     */
    private null|ClusterWatcher $watcher = null;

    /**
     * The Number of worker processes to be managed by the cluster.
     */
    private int $workerCount;

    /**
     * The semaphore instance for synchronizing parcels.
     */
    private null|Semaphore $semaphore;

    /**
     * The mutex instance for synchronizing parcels.
     */
    private null|SemaphoreMutex $mutex;

    /**
     * The shared memory parcel instance for sharing data between workers.
     */
    private null|Parcel $parcel;

    /**
     * The channel instance for communicating between the cluster and its workers.
     */
    private null|Channel $channel;

    /**
     * Create a new {@see Cluster} instance.
     *
     * @param string $entrypoint Path to the entrypoint file for executes the {@see ClusterWorkerInterface} instance.
     * @param LoggerInterface $logger Logger instance for logging cluster activities.
     * @param int|null $workerCount Optional number of workers to start. If null, the number of CPU cores will be used.
     */
    public function __construct(string $entrypoint, EventDispatcherInterface $dispatcher, LoggerInterface $logger, null|int $workerCount = null)
    {
        $this->entrypoint = $entrypoint;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->workerCount = $workerCount ?? countCpuCores();
    }

    /**
     * @inheritDoc
     */
    public function getMutex(): Mutex
    {
        if (null === $this->mutex) {
            throw new RuntimeException('Failed to access the shared mutex, the cluster is not started.');
        }

        return $this->mutex;
    }

    /**
     * @inheritDoc
     */
    public function getParcel(): Parcel
    {
        if (null === $this->parcel) {
            throw new RuntimeException('Failed to access the shared parcel, the cluster is not started.');
        }

        return $this->parcel;
    }

    public function getChannel(): Channel
    {
        if (null === $this->channel) {
            throw new RuntimeException('Failed to access the shared channel, the cluster is not started.');
        }

        return $this->channel;
    }

    private array $objects = [];

    public function getOrCreateSharedResource(string $identifier, mixed $default): SharedResource
    {
        if (null === $this->parcel) {
            throw new RuntimeException('Failed to access the shared resource, the cluster is not started.');
        }

        if (!isset($this->objects[$identifier])) {
            $this->objects[$identifier] = new SharedResource($identifier, $this->getParcel(), $default);
        }

        return $this->objects[$identifier];
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

        $this->initialize();

        $this->logger->info('Cluster started with {workers} workers.', ['workers' => $workers]);

        $this->dispatcher->dispatch(new ClusterStartedEvent($workers));
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

        $this->initialize();

        $this->logger->info('Cluster restarted.');

        $this->dispatcher->dispatch(new ClusterRestartedEvent());
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

        $this->dispatcher->dispatch(new ClusterStoppedEvent());
    }

    /**
     * Send the semaphore and parcel to the worker processes.
     *
     * @throws RuntimeException If the cluster is not started.
     */
    private function initialize(): void
    {
        if (null === $this->watcher) {
            throw new RuntimeException('Failed to send semaphore and parcel to workers, the cluster is not started.');
        }


        $this->semaphore = $this->semaphore ?? PosixSemaphore::create(1, permissions: 0666);
        $this->mutex = $this->mutex ?? new SemaphoreMutex($this->semaphore);
        $this->parcel = $this->parcel ?? SharedMemoryParcel::create($this->mutex, null);
        $this->channel = new Internal\ClusterChannel($this->watcher);

        $this->watcher->broadcast($this->semaphore->getKey());
        $this->watcher->broadcast($this->parcel->getKey());

    }
}
