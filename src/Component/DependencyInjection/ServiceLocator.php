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

use Psl\Vec;

/**
 * @template T of object
 *
 * @implements ServiceLocatorInterface<T>
 */
final readonly class ServiceLocator implements ServiceLocatorInterface
{
    /**
     * The container used to retrieve the services.
     */
    private ContainerInterface $container;

    /**
     * The type of the services.
     *
     * @var class-string<T>
     */
    private string $type;

    /**
     * An array of container services identifiers, indexed by the locator service identifier.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $services;

    /**
     * @param ContainerInterface $container The container used to retrieve the services.
     * @param class-string<T> $type The type of the services.
     * @param array<non-empty-string, non-empty-string> $services An array of container services identifiers, indexed by the locator service identifier.
     */
    public function __construct(ContainerInterface $container, string $type, array $services)
    {
        $this->container = $container;
        $this->type = $type;
        $this->services = $services;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAvailableServices(): array
    {
        return Vec\keys($this->services);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function has(string $id): bool
    {
        return '' !== $id && isset($this->services[$id]);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function get(string $id): object
    {
        if ('' === $id || !isset($this->services[$id])) {
            throw Exception\ServiceNotFoundException::forService($id);
        }

        return $this->container->getTyped($this->services[$id], $this->type);
    }
}
