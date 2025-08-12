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

namespace Neu\Framework\Listener\Database;

use Neu\Component\Database\DatabaseManagerInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\Http\Server\Event\ServerStoppingEvent;
use Override;

/**
 * A listener that closes all databases when the server is stopping.
 *
 * @implements ListenerInterface<ServerStoppingEvent>
 *
 * @psalm-suppress MissingThrowsDocblock
 */
#[Listener(ServerStoppingEvent::class)]
final readonly class ServerStoppingEventListener implements ListenerInterface
{
    /**
     * The database manager used to close all database, or null if not set.
     */
    private null|DatabaseManagerInterface $databaseManager;

    /**
     * Create a new {@see ServerStoppingEventListener} instance.
     *
     * @param null|DatabaseManagerInterface $databaseManager The database manager used to close all database, or null if not set.
     */
    public function __construct(null|DatabaseManagerInterface $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(object $event): object
    {
        if (null !== $this->databaseManager) {
            $databases = $this->databaseManager->getAvailableDatabases();
            foreach ($databases as $database) {
                $database->close();
            }
        }

        return $event;
    }
}
