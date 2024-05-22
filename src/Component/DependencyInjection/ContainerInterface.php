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

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Returns the project instance.
     *
     * @return Project
     */
    public function getProject(): Project;

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Finds a service of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws Exception\ServiceNotFoundException No entry was found for **this** identifier.
     * @throws Exception\ExceptionInterface Error while retrieving the entry.
     */
    public function get(string $id): object;

    /**
     * Finds a service of the container by its identifier and returns it.
     *
     * Unlike {@see get()}, this method verifies that the service is of the given type.
     *
     * @template T
     *
     * @param non-empty-string $id The identifier of the service.
     * @param class-string<T> $type The type of the service.
     *
     * @throws Exception\ExceptionInterface Error while retrieving the entry.
     * @throws Exception\ServiceNotFoundException No entry was found for **this** identifier.
     *
     * @return T
     */
    public function getTyped(string $id, string $type): object;

    /**
     * Retrieves all the services for a given type.
     *
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return iterable<T>
     */
    public function getInstancesOf(string $type): iterable;

    /**
     * Retrieves all the services for a given attribute.
     *
     * @param class-string $attribute
     *
     * @return iterable<object>
     */
    public function getAttributed(string $attribute): iterable;

    /**
     * Returns a service locator for the given type and services.
     *
     * @template T of object
     *
     * @param class-string<T> $type
     * @param array<non-empty-string, non-empty-string> $services
     *
     * @return ServiceLocatorInterface<T>
     */
    public function getLocator(string $type, array $services): ServiceLocatorInterface;
}
