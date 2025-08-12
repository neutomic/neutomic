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

namespace Neu\Component\Database\Notification\Postgres;

use Amp\Postgres\PostgresListener as AmpPostgresListener;
use Iterator;
use Neu\Component\Database\Notification\ListenerInterface;
use Neu\Component\Database\Notification\Notification;
use Override;

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
    #[Override]
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isAlive(): bool
    {
        return $this->listener->isListening();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function close(): void
    {
        $this->listener->unlisten();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function listen(): Iterator
    {
        foreach ($this->listener as $notification) {
            yield new Notification($notification->channel, $notification->payload, $notification->pid);
        }
    }
}
