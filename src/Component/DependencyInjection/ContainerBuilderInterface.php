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

use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\DependencyInjection\Definition\DefinitionInterface;

interface ContainerBuilderInterface
{
    /**
     * Get the project instance.
     *
     * @return Project The project instance.
     */
    public function getProject(): Project;

    /**
     * Get the configuration container.
     *
     * @return ConfigurationContainerInterface The configuration container.
     */
    public function getConfiguration(): ConfigurationContainerInterface;

    /**
     * Add a configuration to the container.
     *
     * The configuration will be merged with the existing configuration.
     *
     * @param ConfigurationContainerInterface|array<array-key, mixed> $configuration The configuration to add.
     */
    public function addConfiguration(ConfigurationContainerInterface|array $configuration): void;

    /**
     * Check if the container has auto-discovery enabled.
     *
     * @return bool True if auto-discovery is enabled, false otherwise.
     */
    public function hasAutoDiscovery(): bool;

    /**
     * Enable or disable auto-discovery for the container.
     *
     * Auto-discovery will automatically register services from the project's source, and entry point.
     *
     * @param bool $autoDiscovery True to enable auto-discovery, false to disable it.
     */
    public function setAutoDiscovery(bool $autoDiscovery): void;

    /**
     * Return whether the container has an extension.
     *
     * @param class-string<ExtensionInterface> $extension
     *
     * @return bool True if the container has the extension, false otherwise.
     */
    public function hasExtension(string $extension): bool;

    /**
     * Add an extension to the container.
     *
     * @param ExtensionInterface $extension The extension to add.
     */
    public function addExtension(ExtensionInterface $extension): void;

    /**
     * Add extensions to the container.
     *
     * @param list<ExtensionInterface> $extensions The extensions to add.
     */
    public function addExtensions(array $extensions): void;

    /**
     * Add a container hook to the builder.
     *
     * @param HookInterface $hook The hook to add.
     */
    public function addHook(HookInterface $hook): void;

    /**
     * Add container hooks to the builder.
     *
     * @param list<HookInterface> $hooks The hooks to add.
     */
    public function addHooks(array $hooks): void;

    /**
     * Check if a definition exists in the container.
     *
     * @param non-empty-string $id The identifier of the definition.
     *
     * @return bool True if the definition exists, false otherwise.
     */
    public function hasDefinition(string $id): bool;

    /**
     * Get a definition from the container.
     *
     * @param non-empty-string $id The identifier of the definition.
     *
     * @throws Exception\ServiceNotFoundException If the definition does not exist.
     */
    public function getDefinition(string $id): DefinitionInterface;

    /**
     * Get all definitions from the container.
     *
     * @return array<non-empty-string, DefinitionInterface> The definitions.
     */
    public function getDefinitions(): array;

    /**
     * Add a definition to the container.
     *
     * @template T of object
     *
     * @param DefinitionInterface<T> $definition The definition to add.
     */
    public function addDefinition(DefinitionInterface $definition): void;

    /**
     * Add definitions to the container.
     *
     * @param list<DefinitionInterface> $definitions The definitions to add.
     */
    public function addDefinitions(array $definitions): void;

    /**
     * Add a processor for the container.
     *
     * The processor will be added to all service definitions, current and future.
     *
     * @param ProcessorInterface $processor The processor to register.
     */
    public function addProcessor(ProcessorInterface $processor): void;

    /**
     * Register a processor for a specific type.
     *
     * All services that are an instance of the given type will be processed by the processor.
     *
     * The processor will be added to all service definitions that are an instance of the type, current and future.
     *
     * @param class-string $type The type to register the processor for.
     * @param ProcessorInterface $processor The processor to register.
     */
    public function addProcessorForInstanceOf(string $type, ProcessorInterface $processor): void;

    /**
     * Register a processor for an attribute.
     *
     * All services that have the attribute will be processed by the processor.
     *
     * The processor will be added to all service definitions that have the attribute, current and future.
     *
     * @param class-string $attribute The attribute to register the processor for.
     * @param ProcessorInterface $processor The processor to register.
     */
    public function addProcessorForAttribute(string $attribute, ProcessorInterface $processor): void;

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
