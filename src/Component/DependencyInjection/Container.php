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
use Neu\Component\DependencyInjection\Exception\DisposedObjectException;

use function count;

/**
 * The service container.
 *
 * @psalm-suppress InvalidReturnType
 */
final class Container implements ContainerInterface
{
    /**
     * The project instance.
     *
     * @var Project
     */
    private Project $project;

    /**
     * The service definitions.
     *
     * @var array<non-empty-string, DefinitionInterface>
     */
    private array $definitions;

    /**
     * A map of type identifiers to service identifiers.
     *
     * @var array<class-string, list<non-empty-string>>
     */
    private array $typeIdsMap;

    /**
     * A map of alias identifiers to service identifiers.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $aliasIdMap;

    /**
     * Whether the container has been disposed.
     *
     * @var bool
     */
    private bool $isDisposed = false;

    /**
     * @param array<non-empty-string, DefinitionInterface> $definitions
     */
    public function __construct(Project $project, array $definitions)
    {
        $this->project = $project;
        $this->definitions = $definitions;

        $typeIdsMap = [];
        $aliasIdMap = [];
        foreach ($definitions as $id => $definition) {
            $type = $definition->getType();
            $typeIds = $typeIdsMap[$type] ?? [];
            $typeIds[] = $id;
            $typeIdsMap[$type] = $typeIds;
            foreach ($definition->getAliases() as $alias) {
                $aliasIdMap[$alias] = $id;
            }
        }

        $this->typeIdsMap = $typeIdsMap;
        $this->aliasIdMap = $aliasIdMap;
    }

    /**
     * @inheritDoc
     */
    public function getProject(): Project
    {
        DisposedObjectException::guard($this);

        return $this->project;
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        DisposedObjectException::guard($this);

        if ('' === $id) {
            return false;
        }

        if ($id === Project::class || $id === ProjectMode::class) {
            return true;
        }

        return isset($this->definitions[$id]) || isset($this->aliasIdMap[$id]) || isset($this->typeIdsMap[$id]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        DisposedObjectException::guard($this);

        if ('' === $id) {
            throw Exception\RuntimeException::forEmptyServiceId();
        }

        if ($id === Project::class) {
            return $this->project;
        }

        if ($id === ProjectMode::class) {
            return $this->project->mode;
        }

        if (isset($this->definitions[$id])) {
            return $this->definitions[$id]->resolve($this);
        }

        if (isset($this->aliasIdMap[$id])) {
            return $this->definitions[$this->aliasIdMap[$id]]->resolve($this);
        }

        if (isset($this->typeIdsMap[$id])) {
            $typeIds = $this->typeIdsMap[$id];
            if (count($typeIds) === 1) {
                return $this->definitions[$typeIds[0]]->resolve($this);
            }

            throw Exception\AmbiguousServiceException::forType($id, $typeIds);
        }

        throw Exception\ServiceNotFoundException::forService($id);
    }

    /**
     * @inheritDoc
     */
    public function getTyped(string $id, string $type): object
    {
        DisposedObjectException::guard($this);

        /** @psalm-suppress MissingThrowsDocblock - Psr exception is never thrown */
        $service = $this->get($id);
        if ($service instanceof $type) {
            return $service;
        }

        throw new Exception\InvalidServiceTypeException('Service "' . $id . '" is not an instance of expected type "' . $type . '"');
    }

    /**
     * @inheritDoc
     */
    public function getInstancesOf(string $type): iterable
    {
        DisposedObjectException::guard($this);

        foreach ($this->definitions as $definition) {
            if ($definition->isInstanceOf($type)) {
                yield $definition->resolve($this);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributed(string $attribute): iterable
    {
        DisposedObjectException::guard($this);

        foreach ($this->definitions as $definition) {
            if ($definition->hasAttribute($attribute)) {
                yield $definition->resolve($this);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getLocator(string $type, array $services): ServiceLocatorInterface
    {
        DisposedObjectException::guard($this);

        return new ServiceLocator($this, $type, $services);
    }

    /**
     * @inheritDoc
     */
    public function isDisposed(): bool
    {
        return $this->isDisposed;
    }

    /**
     * @inheritDoc
     */
    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        try {
            foreach ($this->getInstancesOf(DisposableInterface::class) as $instance) {
                $instance->dispose();
            }
        } finally {
            $this->isDisposed = true;
            $this->definitions = [];
            $this->typeIdsMap = [];
            $this->aliasIdMap = [];
        }
    }

    /**
     * Dispose of the container on destruction.
     */
    public function __destruct()
    {
        $this->dispose();
    }
}
