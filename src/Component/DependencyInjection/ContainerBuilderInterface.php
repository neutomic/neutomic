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

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Configuration\Loader\LoaderInterface;

interface ContainerBuilderInterface
{
    /**
     * Add a configuration resource to the container builder.
     *
     * The configuration resource can be a file path, a directory path, an array of configuration, or any other supported type.
     *
     * @param mixed $resource The configuration resource to add.
     */
    public function addConfigurationResource(mixed $resource): void;

    /**
     * Add a configuration loader to the container builder.
     *
     * The configuration loader will be used to load the configuration resources.
     *
     * @param LoaderInterface $loader The configuration loader to add.
     */
    public function addConfigurationLoader(LoaderInterface $loader): void;

    /**
     * Check if the container builder has auto-discovery enabled.
     *
     * @return bool True if auto-discovery is enabled, false otherwise.
     */
    public function hasAutoDiscovery(): bool;

    /**
     * Enable or disable auto-discovery for the container builder.
     *
     * Auto-discovery will automatically register services from the project's source, and entry point.
     *
     * @param bool $autoDiscovery True to enable auto-discovery, false to disable it.
     */
    public function setAutoDiscovery(bool $autoDiscovery): void;

    /**
     * Add a path to the builder for auto-discovery.
     *
     * @param non-empty-string $path The path to add.
     */
    public function addPathForAutoDiscovery(string $path): void;

    /**
     * Add an extension to the container builder.
     *
     * @param ExtensionInterface $extension The extension to add.
     */
    public function addExtension(ExtensionInterface $extension): void;

    /**
     * Add extensions to the container builder.
     *
     * @param list<ExtensionInterface> $extensions The extensions to add.
     */
    public function addExtensions(array $extensions): void;

    /**
     * Get the registry.
     *
     * @return RegistryInterface The registry.
     */
    public function getRegistry(): RegistryInterface;

    /**
     * Build the container.
     *
     * The resulting container will be a read-only snapshot of the current state of the builder.
     *
     * @throws Exception\ExceptionInterface If an error occurs while building the container.
     *
     * @return ContainerInterface The built container.
     */
    public function build(): ContainerInterface;
}
