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
use Neu\Component\Http\Runtime\Middleware\MiddlewareQueueInterface;

/**
 * Interface PluginInterface.
 *
 * Defines the contract for a plugin in the Neu framework. Plugins can add routes, commands,
 * event listeners, middleware, and perform initialization and cleanup tasks.
 *
 * @package Neu\Framework\Plugin
 */
interface PluginInterface
{
    /**
     * Boot the plugin.
     *
     * This method is called to initialize the plugin. It can be used to set up services,
     * and perform initialization tasks.
     *
     * @param ContainerInterface $container The dependency injection container.
     */
    public function boot(ContainerInterface $container): void;

    /**
     * Register routes.
     *
     * This method is called to register routes with the router. Plugins can add their own routes
     * to the application's routing table.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param RouterRegistryInterface $registry The router registry.
     */
    public function route(ContainerInterface $container, RouterRegistryInterface $registry): void;

    /**
     * Enqueue middleware.
     *
     * This method is called to enqueue middleware into the middleware queue. Plugins can add
     * their own middleware to be executed during the request lifecycle.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param MiddlewareQueueInterface $queue The middleware queue.
     */
    public function enqueue(ContainerInterface $container, MiddlewareQueueInterface $queue): void;

    /**
     * Register event listeners.
     *
     * This method is called to register event listeners with the event dispatcher. Plugins can add
     * listeners to handle various events within the application.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param EventRegistryInterface $registry The event listener registry.
     */
    public function listen(ContainerInterface $container, EventRegistryInterface $registry): void;

    /**
     * Register commands.
     *
     * This method is called to register console commands with the application. Plugins can add
     * their own commands to the application's command-line interface.
     *
     * @param ContainerInterface $container The dependency injection container.
     * @param ConsoleRegistryInterface $registry The console registry.
     */
    public function command(ContainerInterface $container, ConsoleRegistryInterface $registry): void;

    /**
     * Shutdown the plugin.
     *
     * This method is called to perform any cleanup tasks before the application shuts down.
     *
     * @param ContainerInterface $container The dependency injection container.
     */
    public function shutdown(ContainerInterface $container): void;
}
