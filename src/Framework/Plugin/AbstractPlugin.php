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

namespace Neu\Framework\Plugin;

use Neu\Component\Console\Command\Registry\RegistryInterface as ConsoleRegistryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\EventDispatcher\Listener\Registry\RegistryInterface as EventRegistryInterface;
use Neu\Component\Http\Router\Registry\RegistryInterface as RouterRegistryInterface;
use Neu\Component\Http\Router\RouteCollector;
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;

/**
 * Provides a base implementation of the {@see PluginInterface}, allowing derived classes to
 * override only the methods they need, making it easier to create plugins.
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function boot(ContainerInterface $container): void
    {
        // Default implementation does nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function route(ContainerInterface $container, RouterRegistryInterface $registry, RouteCollector $collector): void
    {
        // Default implementation does nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function enqueue(ContainerInterface $container, MiddlewareQueueInterface $queue): void
    {
        // Default implementation does nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function listen(ContainerInterface $container, EventRegistryInterface $registry): void
    {
        // Default implementation does nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function command(ContainerInterface $container, ConsoleRegistryInterface $registry): void
    {
        // Default implementation does nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function shutdown(ContainerInterface $container): void
    {
        // Default implementation does nothing
    }
}
