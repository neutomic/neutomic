<?php

declare(strict_types=1);

namespace Neu\Component\Cache\Driver;

use Revolt\EventLoop;

/**
 * Abstract class representing a generic cache driver.
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * The default interval, in seconds, at which to run {@see DriverInterface::prune()}.
     */
    public const int PRUNE_INTERVAL = 300;

    /**
     * The interval, in seconds, at which to run {@see DriverInterface::prune()}.
     */
    private readonly int $pruneInterval;

    /**
     * The identifier of the event loop watcher that triggers the prune operation.
     */
    private readonly string $pruneWatcher;

    /**
     * Constructor for the cache driver.
     *
     * Sets up the pruning operation to run at a defined interval using the event loop.
     *
     * The pruning interval defaults to {@see self::PRUNE_INTERVAL} but can be customized by passing
     * a different value.
     *
     * @param positive-int $pruneInterval The interval, in seconds, at which to run {@see DriverInterface::prune()}.
     */
    public function __construct(int $pruneInterval = self::PRUNE_INTERVAL)
    {
        $this->pruneInterval = $pruneInterval;
        $this->pruneWatcher = EventLoop::repeat($this->pruneInterval, $this->prune(...));

        EventLoop::unreference($this->pruneWatcher);
    }

    /**
     * Destructor for the cache driver.
     *
     * Ensures that the event loop watcher for pruning is properly disabled when
     * the cache driver object is destroyed.
     */
    public function __destruct()
    {
        EventLoop::disable($this->pruneWatcher);
    }
}
