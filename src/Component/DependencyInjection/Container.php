<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection;

use Neu\Component\DependencyInjection\Definition\DefinitionInterface;

use function count;

final readonly class Container implements ContainerInterface
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
     * @var list<DefinitionInterface>
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
     * @param array<string, DefinitionInterface> $definitions
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
        return $this->project;
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
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
        return new ServiceLocator($this, $type, $services);
    }
}
