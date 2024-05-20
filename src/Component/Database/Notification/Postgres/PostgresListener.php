<?php

declare(strict_types=1);

namespace Neu\Component\Database\Notification\Postgres;

use Amp\Postgres\PostgresListener as AmpPostgresListener;
use Iterator;
use Neu\Component\Database\Notification\ListenerInterface;
use Neu\Component\Database\Notification\Notification;

final readonly class PostgresListener implements ListenerInterface
{
    /**
     * @param non-empty-string $channel
     */
    public function __construct(
        private AmpPostgresListener $listener,
        private string              $channel,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @inheritDoc
     */
    public function isAlive(): bool
    {
        return $this->listener->isListening();
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->listener->unlisten();
    }

    /**
     * @inheritDoc
     */
    public function listen(): Iterator
    {
        foreach ($this->listener as $notification) {
            yield new Notification($notification->channel, $notification->payload, $notification->pid);
        }
    }
}
