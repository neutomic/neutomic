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

namespace Neu\Framework\Listener\Broadcast;

use Neu\Component\Broadcast\HubManagerInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\Http\Server\Event\ServerStoppingEvent;

/**
 * A listener that closes all hubs when the server is stopping.
 *
 * @implements ListenerInterface<ServerStoppingEvent>
 *
 * @psalm-suppress MissingThrowsDocblock
 */
#[Listener(ServerStoppingEvent::class)]
final readonly class ServerStoppingEventListener implements ListenerInterface
{
    /**
     * The hub manager used to close all hubs, or null if not set.
     */
    private null|HubManagerInterface $hubManager;

    /**
     * Create a new {@see ServerStoppingEventListener} instance.
     *
     * @param null|HubManagerInterface $hubManager The hub manager used to close all hubs, or null if not set.
     */
    public function __construct(null|HubManagerInterface $hubManager)
    {
        $this->hubManager = $hubManager;
    }

    /**
     * @inheritDoc
     */
    public function process(object $event): object
    {
        if (null !== $this->hubManager) {
            $hubs = $this->hubManager->getAvailableHubs();
            foreach ($hubs as $hub) {
                $hub->close();
            }
        }

        return $event;
    }
}
