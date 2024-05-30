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

namespace Neu\Framework\DependencyInjection\Factory\Listener\Broadcast;

use Neu\Component\Broadcast\HubManagerInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Listener\Broadcast\ServerStoppingEventListener;

/**
 * The factory for the server stopping event listener.
 *
 * @implements FactoryInterface<ServerStoppingEventListener>
 */
final readonly class ServerStoppingEventListenerFactory implements FactoryInterface
{
    /**
     * The hub manager service identifier.
     *
     * @var non-empty-string|null
     */
    private null|string $hubManager;

    /**
     * Create a new {@see ServerStoppingEventListenerFactory} instance.
     *
     * @param null|non-empty-string $hubManager The hub manager service identifier.
     */
    public function __construct(null|string $hubManager = null)
    {
        $this->hubManager = $hubManager;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        if (null === $this->hubManager) {
            if ($container->has(HubManagerInterface::class)) {
                $hubManager = $container->getTyped(HubManagerInterface::class, HubManagerInterface::class);
            } else {
                $hubManager = null;
            }
        } else {
            $hubManager = $container->getTyped($this->hubManager, HubManagerInterface::class);
        }

        return new ServerStoppingEventListener($hubManager);
    }
}
