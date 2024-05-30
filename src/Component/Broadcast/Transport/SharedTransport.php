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

namespace Neu\Component\Broadcast\Transport;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Neu\Component\Broadcast\Exception\ClosedTransportException;
use Neu\Component\Http\Server\SharedResource;
use Revolt\EventLoop;

/**
 * A local transport mechanism that sends and receives messages locally (in the same process).
 */
final class SharedTransport implements TransportInterface
{
    /**
     * The channels and their messages.
     *
     * @var null|SharedResource<non-empty-string, list<mixed>>
     */
    private ?SharedResource $channels = null;

    /**
     * The listeners and their sources.
     *
     * @var array<non-empty-string, Queue<mixed>>
     */
    private array $listeners = [];

    /**
     * The watcher for the transport.
     */
    private ?string $watcher = null;

    /**
     * The interval in seconds between each pull operation.
     */
    private float $pullInterval;

    /**
     * Create a new {@see LocalTransport} instance.
     *
     * @param SharedResource<non-empty-string, list<mixed>> $channels The shared object to use for channels.
     * @param float $pullInterval The interval in seconds between each pull operation.
     */
    public function __construct(SharedResource $channels, float $pullInterval = 1.0)
    {
        $this->channels = $channels;
        $this->pullInterval = $pullInterval;
    }

    /**
     * @inheritDoc
     */
    public function send(string $channel, mixed $message): void
    {
        if ($this->channels === null) {
            throw new ClosedTransportException('The transport is closed.');
        }

        $this->channels->synchronized(function (array $channels) use ($channel, $message): array {
            $channels[$channel][] = $message;

            return $channels;
        });

        $this->run();
    }

    /**
     * @inheritDoc
     */
    public function isListening(string $channel): bool
    {
        return isset($this->listeners[$channel]);
    }

    /**
     * @inheritDoc
     */
    public function listen(string $channel): ConcurrentIterator
    {
        if ($this->channels === null) {
            throw new ClosedTransportException('The transport is closed.');
        }

        $source = new Queue();

        $this->listeners[$channel] = $source;

        $this->run();

        return $source->iterate();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if ($this->watcher !== null) {
            EventLoop::cancel($this->watcher);
            $this->watcher = null;
        }

        $listeners = $this->listeners;
        foreach ($listeners as $listener) {
            $listener->complete();
        }

        $this->channels = null;
        $this->listeners = [];
    }

    /**
     * @inheritDoc
     */
    public function isClosed(): bool
    {
        return $this->channels === null;
    }

    /**
     * Runs the transport, pulling messages from the channels and pushing them to the listeners.
     */
    private function run(): void
    {
        if ($this->watcher !== null) {
            if (!EventLoop::isEnabled($this->watcher)) {
                EventLoop::enable($this->watcher);
            }

            return;
        }

        $this->watcher = EventLoop::repeat($this->pullInterval * 1000, function (): void {
            if (empty($this->listeners)) {
                EventLoop::disable($this->watcher);

                return;
            }

            $channels = $this->channels->unwrap();
            foreach ($this->listeners as $channel => $source) {
                if (!isset($channels[$channel])) {
                    continue;
                }

                $messages = $this->channels[$channel];
                unset($this->channels[$channel]);
                foreach ($messages as $message) {
                    $source->push($message);
                }
            }
        });
    }
}
