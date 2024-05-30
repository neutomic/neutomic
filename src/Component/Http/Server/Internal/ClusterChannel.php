<?php

namespace Neu\Component\Http\Server\Internal;

use Amp\Cancellation;
use Amp\Cluster\ClusterWatcher;
use Amp\DeferredFuture;
use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\DisposedException;
use Amp\Sync\Channel;
use Amp\Sync\ChannelException;
use Closure;
use Revolt\EventLoop;

final class ClusterChannel implements Channel
{
    private ClusterWatcher $watcher;
    private ConcurrentIterator $iterator;
    private DeferredFuture $onClose;
    private array $suspensions = [];

    public function __construct(ClusterWatcher $watcher)
    {
        $this->watcher = $watcher;
        $this->iterator = $watcher->getMessageIterator();
        $this->onClose = new DeferredFuture();

        EventLoop::defer(function () {
            try {
                foreach ($this->iterator as $message) {
                    $suspension = array_shift($this->suspensions);
                    if ($suspension) {
                        $suspension->resume($message);
                    }

                    $this->send($message);
                }
            } catch (DisposedException) {
                // Channel was closed
            }
        });
    }

    public function receive(?Cancellation $cancellation = null): mixed
    {
        $suspension = EventLoop::getSuspension();
        $this->suspensions[] = $suspension;

        return $suspension->suspend();
    }

    public function send(mixed $data): void
    {
        $this->watcher->broadcast($data);
    }

    public function isClosed(): bool
    {
        return $this->onClose->isComplete();
    }

    public function close(): void
    {
        $this->iterator->dispose();
        foreach ($this->suspensions as $suspension) {
            $suspension->throw(new ChannelException('The channel closed while waiting to receive the next value'));
        }

        if (!$this->onClose->isComplete()) {
            $this->onClose->complete();
        }
    }

    public function onClose(Closure $onClose): void
    {
        $this->onClose->getFuture()->finally($onClose);
    }
}
