<?php

declare(strict_types=1);

namespace Neu\Framework\Internal;

use Amp\Cluster\Cluster;
use Amp\File;
use Amp\File\Driver\EioFilesystemDriver;
use Amp\File\Driver\ParallelFilesystemDriver;
use Amp\File\Driver\StatusCachingFilesystemDriver;
use Amp\File\Driver\UvFilesystemDriver;
use Revolt\EventLoop;

/**
 * Internal utilities.
 *
 * @internal
 */
final readonly class Internal
{
    /**
     * Fixes the Amp filesystem for worker processes.
     *
     * This method should be called in the worker process to ensure the filesystem is correctly configured.
     */
    public static function fixAmpFilesystemForWorker(): void
    {
        if (!Cluster::isWorker()) {
            return;
        }

        /**
         * @note: this still not 100% correct, UV fs driver will not be enabled if tracing is enabled.
         *  but it is better than {@see File\createDefaultDriver()} which will always use blocking driver in workers, and skip caching.
         */
        $eventLoopDriver = EventLoop::getDriver();
        if (UvFilesystemDriver::isSupported($eventLoopDriver)) {
            /** @var EventLoop\Driver\UvDriver $eventLoopDriver */
            $driver = new UvFilesystemDriver($eventLoopDriver);
        } elseif (EioFilesystemDriver::isSupported()) {
            $driver = new EioFilesystemDriver($eventLoopDriver);
        } else {
            $driver = new ParallelFilesystemDriver();
        }

        $driver = new StatusCachingFilesystemDriver($driver);

        // force the filesystem to use the driver we just created
        File\filesystem($driver);
    }
}
