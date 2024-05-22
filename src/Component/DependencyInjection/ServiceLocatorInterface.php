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
     * @psalm-assert-if-true non-empty-string $id
     */
    public function has(string $id): bool;

    /**
     * Returns a service by its identifier.
     *
     * @throws Exception\ServiceNotFoundException If the service is not found.
     * @throws Exception\ExceptionInterface Error while retrieving the entry.
     *
     * @return T
     *
     * @psalm-assert non-empty-string $id
     */
    public function get(string $id): object;
}
