<?php

declare(strict_types=1);

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
    public function getAvailableServices(): array
    {
        return Vec\keys($this->services);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        if (!isset($this->services[$id])) {
            throw Exception\ServiceNotFoundException::forService($id);
        }

        return $this->container->getTyped($this->services[$id], $this->type);
    }
}
