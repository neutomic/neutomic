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

namespace Neu\Framework;

use Neu\Framework\Exception\RuntimeException;
use Neu\Framework\Plugin\PluginInterface;

/**
 * Defines the contract for an engine that initializes the project, manages plugins, and runs the application.
 */
interface EngineInterface
{
    /**
     * Inject a plugin into the engine.
     *
     * This method allows a plugin to be added to the engine. Plugins can define routes, commands,
     * listeners, middlewares, and other components to extend the application's functionality.
     *
     * @param PluginInterface $plugin The plugin to inject.
     */
    public function inject(PluginInterface $plugin): void;

    /**
     * Run the application.
     *
     * This method starts the application, executing any necessary initialization and then running
     * the main application logic, such as starting a server or executing console commands.
     *
     * @throws RuntimeException If an error occurs while running the engine.
     */
    public function run(Mode $mode = Mode::Application): void;
}
