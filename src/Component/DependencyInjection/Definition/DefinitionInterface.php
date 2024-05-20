<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Definition;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\DependencyInjection\ProcessorInterface;

/**
 * @template T of object
 */
interface DefinitionInterface
{
    /**
     * Get the definition identifier.
     *
     * @return non-empty-string
     */
    public function getId(): string;

    /**
     * Set the definition identifier.
     *
     * @param non-empty-string $id
     */
    public function setId(string $id): void;

    /**
     * Get the type.
     *
     * @return class-string<T>
     */
    public function getType(): string;

    /**
     * Set the type.
     *
     * @param class-string<T> $type
     */
    public function setType(string $type): void;

    /**
     * Get the factory.
     *
     * @return null|FactoryInterface<T>
     */
    public function getFactory(): ?FactoryInterface;

    /**
     * Set the factory.
     *
     * @param null|FactoryInterface<T> $factory
     */
    public function setFactory(?FactoryInterface $factory): static;

    /**
     * Get the processors.
     *
     * @return list<ProcessorInterface<T>>
     */
    public function getProcessors(): array;

    /**
     * Add a processor.
     *
     * @param ProcessorInterface<T> $processor
     */
    public function addProcessor(ProcessorInterface $processor): static;

    /**
     * Set the processors.
     *
     * @param list<ProcessorInterface<T>> $processors
     */
    public function setProcessors(array $processors): static;

    /**
     * Get the aliases of the definition.
     *
     * @return list<non-empty-string>
     */
    public function getAliases(): array;

    /**
     * Add an alias.
     *
     * @param non-empty-string $name
     */
    public function addAlias(string $name): static;

    /**
     * Set the aliases.
     *
     * @param list<non-empty-string> $aliases
     */
    public function setAliases(array $aliases): static;

    /**
     * Check if the definition is an instance of a specific type.
     *
     * @template I of object
     *
     * @param class-string<I> $type
     *
     * @return bool
     *
     * @psalm-assert-if-true T is I
     */
    public function isInstanceOf(string $type): bool;

    /**
     * Check if the type of the definition uses a specific attribute.
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function hasAttribute(string $name): bool;

    /**
     * Resolve the definition.
     *
     * @param ContainerInterface $container
     *
     * @throws ExceptionInterface If an error occurs.
     *
     * @return T
     */
    public function resolve(ContainerInterface $container): object;
}
