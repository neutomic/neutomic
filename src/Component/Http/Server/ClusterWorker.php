<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server;

use Amp\Cluster\Cluster;
use Neu\Component\EventDispatcher\EventDispatcherInterface;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Server\Event\ClusterWorkerStartedEvent;
use Neu\Component\Http\Server\Event\ClusterWorkerStoppedEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final readonly class ClusterWorker implements ClusterWorkerInterface
{
    /**
     * The server instance to be managed by the worker.
     */
    private ServerInterface $server;

    /**
     * The event dispatcher for handling worker events.
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The logger instance for logging cluster activities.
     */
    private LoggerInterface $logger;

    /**
     * Create a new {@see ClusterWorker} instance.
     *
     * @param ServerInterface $server The server instance to be managed by the worker.
     * @param EventDispatcherInterface $dispatcher The event dispatcher for handling worker events.
     */
    public function __construct(ServerInterface $server, EventDispatcherInterface $dispatcher, LoggerInterface $logger = new NullLogger())
    {
        $this->server = $server;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        $workerId = Cluster::getContextId();
        if ($workerId === null) {
            throw new RuntimeException('Attempted to start a cluster worker outside of a cluster worker context.');
        }

        $this->logger->notice('Cluster worker "{id}" is starting...', ['id' => $workerId]);

        try {
            $this->server->start();
        } catch (Throwable $exception) {
            $this->logger->error('Cluster worker "{id}" failed to start: {message}', [
                'id' => $workerId,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            throw $exception;
        }

        $this->logger->notice('Cluster worker "{id}" has started.', ['id' => $workerId]);

        $this->dispatcher->dispatch(new ClusterWorkerStartedEvent($workerId));
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
    {
        $workerId = Cluster::getContextId();
        if ($workerId === null) {
            throw new RuntimeException('Attempted to stop a cluster worker outside of a cluster worker context.');
        }

        $this->logger->notice('Cluster worker "{id}" is stopping...', ['id' => $workerId]);

        try {
            $this->server->stop();
        } catch (Throwable $exception) {
            $this->logger->error('Cluster worker "{id}" failed to stop: {message}', [
                'id' => $workerId,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            throw $exception;
        }

        $this->logger->notice('Cluster worker "{id}" has stopped.', ['id' => $workerId]);

        $this->dispatcher->dispatch(new ClusterWorkerStoppedEvent($workerId));
    }
}
