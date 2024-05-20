<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection;

use Psr\Container\ContainerInterface;

/**
 * @template T of object
 */
interface ServiceLocatorInterface extends ContainerInterface
{
    /**
     * Returns a list services identifiers available in the locator.
     *
     * @return list<non-empty-string>
     */
    public function getAvailableServices(): array;

    /**
     * Returns true if the locator has a service identified by the given identifier.
     *
     * @param non-empty-string $id
     */
    public function has(string $id): bool;

    /**
     * Returns a service by its identifier.
     *
     * @param non-empty-string $id
     *
     * @throws Exception\ServiceNotFoundException If the service is not found.
     * @throws Exception\ExceptionInterface Error while retrieving the entry.
     *
     * @return T
     */
    public function get(string $id): object;
}
