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

namespace Neu\Framework\DependencyInjection\Factory\Listener\Database;

use Neu\Component\Database\DatabaseManagerInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Listener\Database\ServerStoppingEventListener;

/**
 * The factory for the server stopping event listener.
 *
 * @implements FactoryInterface<ServerStoppingEventListener>
 */
final readonly class ServerStoppingEventListenerFactory implements FactoryInterface
{
    /**
     * The database manager service identifier.
     *
     * @var non-empty-string|null
     */
    private null|string $databaseManager;

    /**
     * Create a new {@see ServerStoppingEventListenerFactory} instance.
     *
     * @param null|non-empty-string $databaseManager The database manager service identifier.
     */
    public function __construct(null|string $databaseManager = null)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        if (null === $this->databaseManager) {
            if ($container->has(DatabaseManagerInterface::class)) {
                $databaseManager = $container->getTyped(DatabaseManagerInterface::class, DatabaseManagerInterface::class);
            } else {
                $databaseManager = null;
            }
        } else {
            $databaseManager = $container->getTyped($this->databaseManager, DatabaseManagerInterface::class);
        }

        return new ServerStoppingEventListener($databaseManager);
    }
}
