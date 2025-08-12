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

namespace Neu\Framework\DependencyInjection\Factory\Listener\Cache;

use Neu\Component\Cache\StoreManagerInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Framework\Listener\Cache\ServerStoppingEventListener;

/**
 * The factory for the server stopping event listener.
 *
 * @implements FactoryInterface<ServerStoppingEventListener>
 */
final readonly class ServerStoppingEventListenerFactory implements FactoryInterface
{
    /**
     * The store manager service identifier.
     *
     * @var non-empty-string
     */
    private string $storeManager;

    /**
     * Create a new {@see ServerStoppingEventListenerFactory} instance.
     *
     * @param null|non-empty-string $storeManager The store manager service identifier.
     */
    public function __construct(null|string $storeManager = null)
    {
        $this->storeManager = $storeManager ?? StoreManagerInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        $storeManager = $container->getTyped($this->storeManager, StoreManagerInterface::class);

        return new ServerStoppingEventListener($storeManager);
    }
}
