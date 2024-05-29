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

namespace Neu\Framework\Listener\Cache;

use Neu\Component\Cache\StoreManagerInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\Http\Server\Event\ServerStoppingEvent;

/**
 * A listener that closes all stores when the server is stopping.
 *
 * @implements ListenerInterface<ServerStoppingEvent>
 *
 * @psalm-suppress MissingThrowsDocblock
 */
#[Listener(ServerStoppingEvent::class)]
final readonly class ServerStoppingEventListener implements ListenerInterface
{
    /**
     * The store manager used to close all stores.
     */
    private StoreManagerInterface $storeManager;

    /**
     * Create a new {@see ServerStoppingEventListener} instance.
     *
     * @param StoreManagerInterface $storeManager The store manager used to close all stores.
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function process(object $event): object
    {
        $stores = $this->storeManager->getAvailableStores();
        foreach ($stores as $store) {
            $store->close();
        }

        return $event;
    }
}
