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

use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\Exception\ServiceNotFoundException;

interface RegistryInterface
{
    /**
     * Get the project instance.
     *
     * @return Project The project instance.
     */
    public function getProject(): Project;

    /**
     * Add a definition to the orchestrator.
     *
     * @template T of object
     *
     * @param DefinitionInterface<T> $definition The definition to add.
     */
    public function addDefinition(DefinitionInterface $definition): void;

    /**
     * Add definitions to the orchestrator.
     *
     * @param list<DefinitionInterface> $definitions The definitions to add.
     */
    public function addDefinitions(array $definitions): void;

    /**
     * Check if a definition exists in the orchestrator.
     *
     * @param non-empty-string $id The identifier of the definition.
     *
     * @return bool True if the definition exists, false otherwise.
     */
    public function hasDefinition(string $id): bool;

    /**
     * Get a definition from the orchestrator.
     *
     * @param non-empty-string $id The identifier of the definition.
     *
     * @throws ServiceNotFoundException If the definition does not exist.
     */
    public function getDefinition(string $id): DefinitionInterface;

    /**
     * Get all definitions from the orchestrator.
     *
     * @return array<non-empty-string, DefinitionInterface> The definitions.
     */
    public function getDefinitions(): array;

    /**
     * Add a processor for the orchestrator.
     *
     * @param ProcessorInterface $processor The processor to register.
     */
    public function addProcessor(ProcessorInterface $processor): void;

    /**
     * Register a processor for a specific type.
     *
     * All services that are an instance of the given type will be processed by the processor.
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
     * @param class-string $attribute The attribute to register the processor for.
     * @param ProcessorInterface $processor The processor to register.
     */
    public function addProcessorForAttribute(string $attribute, ProcessorInterface $processor): void;

    /**
     * Get the processors for the orchestrator.
     *
     * @return list<ProcessorInterface> The processors for the orchestrator.
     */
    public function getProcessors(): array;

    /**
     * Get the type processors from the orchestrator.
     *
     * @return array<class-string, list<ProcessorInterface>> The processors with the type they are registered for.
     */
    public function getInstanceOfProcessors(): array;

    /**
     * Get the attribute processors from the orchestrator.
     *
     * @return array<class-string, list<ProcessorInterface>> The attribute processors from the orchestrator.
     */
    public function getAttributeProcessors(): array;

    /**
     * Add a container hook to the orchestrator.
     *
     * @param HookInterface $hook The hook to add.
     */
    public function addHook(HookInterface $hook): void;

    /**
     * Add container hooks to the orchestrator.
     *
     * @param list<HookInterface> $hooks The hooks to add.
     */
    public function addHooks(array $hooks): void;

    /**
     * Get the container hooks from the orchestrator.
     *
     * @return list<HookInterface> The container hooks from the orchestrator.
     */
    public function getHooks(): array;
}
