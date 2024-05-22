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

namespace Neu\Component\DependencyInjection\Definition;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
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
    public function getFactory(): null|FactoryInterface;

    /**
     * Set the factory.
     *
     * @param null|FactoryInterface<T> $factory
     */
    public function setFactory(null|FactoryInterface $factory): static;

    /**
     * Get the processors.
     *
     * @return list<ProcessorInterface>
     */
    public function getProcessors(): array;

    /**
     * Add a processor.
     *
     * @param ProcessorInterface $processor
     */
    public function addProcessor(ProcessorInterface $processor): static;

    /**
     * Set the processors.
     *
     * @param list<ProcessorInterface> $processors
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
     * @param class-string $type
     *
     * @throws RuntimeException If unable to check the type.
     *
     * @return bool
     */
    public function isInstanceOf(string $type): bool;

    /**
     * Check if the type of the definition uses a specific attribute.
     *
     * @param class-string $name
     *
     * @throws RuntimeException If unable to check the attribute.
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
