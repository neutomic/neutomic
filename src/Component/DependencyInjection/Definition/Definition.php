<?php

declare(strict_types=1);

namespace Neu\Component\DependencyInjection\Definition;

use Closure;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\ExceptionInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\DependencyInjection\ProcessorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

use function interface_exists;

/**
 * @template T of object
 *
 * @implements DefinitionInterface<T>
 */
final class Definition implements DefinitionInterface
{
    /**
     * The identifier of the service.
     *
     * @var string
     */
    private string $id;

    /**
     * The type of the service.
     *
     * @var class-string<T>
     */
    private string $type;

    /**
     * Factory to create the service.
     *
     * If null, the service is created by instantiating manually.
     *
     * @var null|FactoryInterface
     */
    private ?FactoryInterface $factory = null;

    /**
     * Processors to apply to the service.
     *
     * @var ProcessorInterface[]
     */
    private array $processors = [];

    /**
     * Aliases to apply to the service definition.
     *
     * @var list<non-empty-string>
     */
    private array $aliases = [];

    /**
     * The instance of the service.
     *
     * @var null|object
     */
    private ?object $instance = null;

    /**
     * Determine if the service is currently being resolved.
     *
     * @var bool
     */
    private bool $resolving = false;

    /**
     * @param non-empty-string $id
     * @param class-string<T> $type
     */
    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * Create a new service definition for the given identifier and type.
     *
     * @template S of object
     *
     * @param non-empty-string $id
     * @param class-string<S> $type
     * @param null|FactoryInterface<S> $factory
     *
     * @return static<S>
     */
    public static function create(string $id, string $type, ?FactoryInterface $factory = null): self
    {
        $definition = new self($id, $type);
        $definition->setFactory($factory);

        return $definition;
    }

    /**
     * Create a new service definition for the given type.
     *
     * @template S of object
     *
     * @param class-string<S> $type
     * @param null|FactoryInterface<S> $factory
     *
     * @return static<S>
     */
    public static function ofType(string $type, ?FactoryInterface $factory = null): static
    {
        return self::create($type, $type, $factory);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getFactory(): ?FactoryInterface
    {
        return $this->factory;
    }

    /**
     * @inheritDoc
     */
    public function setFactory(?FactoryInterface $factory): static
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isInstanceOf(string $type): bool
    {
        $reflection = $this->getReflectionClass(false);
        if ($reflection->getName() === $type) {
            return true;
        }

        if (interface_exists($type)) {
            return $reflection->implementsInterface($type);
        }

        return $reflection->isSubclassOf($type);
    }

    public function hasAttribute(string $name): bool
    {
        $attributes = $this->getReflectionClass(false)->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @inheritDoc
     */
    public function addAlias(string $name): static
    {
        $this->aliases[] = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAliases(array $aliases): static
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    /**
     * @inheritDoc
     */
    public function addProcessor(ProcessorInterface $processor): static
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setProcessors(array $processors): static
    {
        $this->processors = $processors;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContainerInterface $container): object
    {
        if ($this->instance !== null) {
            return $this->instance;
        }

        if ($this->resolving) {
            throw new RuntimeException('Failed to resolve service "' . $this->id . '" because it is being resolved, possibly due to a circular dependency.');
        }

        $this->resolving = true;

        try {
            $service = $this->getFactoryClosure()($container);

            foreach ($this->processors as $processor) {
                $service = $processor->process($container, clone $this, $service);
            }

            $this->instance = $service;

            return $service;
        } finally {
            $this->resolving = false;
        }
    }

    /**
     * Get the factory closure.
     *
     * @return (Closure(ContainerInterface): T)
     */
    private function getFactoryClosure(): Closure
    {
        if ($this->factory !== null) {
            return ($this->factory)(...);
        }

        return function (ContainerInterface $container): object {
            $reflection = $this->getReflectionClass();
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                return $reflection->newInstance();
            }

            $parameters = $constructor->getParameters();
            $arguments = [];
            foreach ($parameters as $parameter) {
                $arguments[] = $this->resolveConstructorArgument($container, $parameter, $parameter->getType());
            }

            return $reflection->newInstanceArgs($arguments);
        };
    }

    /**
     * Get the reflection class for the service type.
     *
     * @throws RuntimeException
     *
     * @return ReflectionClass
     */
    private function getReflectionClass(bool $instantiable = true): ReflectionClass
    {
        try {
            $reflection = new ReflectionClass($this->type);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to reflect class "' . $this->type . '"', previous: $e);
        }

        if ($instantiable && !$reflection->isInstantiable()) {
            throw new RuntimeException('Failed to resolve service "' . $this->id . '" because the class "' . $this->type . '" is not instantiable.');
        }

        return $reflection;
    }

    /**
     * Get the reflection property for the service instance.
     *
     * @throws RuntimeException
     *
     * @return ReflectionProperty
     */
    private function getReflectionProperty(object $instance, string $name): ReflectionProperty
    {
        try {
            $reflection = new ReflectionProperty($instance, $name);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to reflect property "' . $name . '" for service "' . $this->id . '"', previous: $e);
        }

        return $reflection;
    }

    private function getReflectionMethod(object $instance, string $name): ReflectionMethod
    {
        try {
            $reflection = new ReflectionMethod($instance, $name);
        } catch (ReflectionException $e) {
            throw new RuntimeException('Failed to reflect method "' . $name . '" for service "' . $this->id . '"', previous: $e);
        }

        return $reflection;
    }

    /**
     * Resolve an argument for a constructor parameter.
     *
     * @param ContainerInterface $container
     * @param ReflectionParameter $parameter
     * @param ReflectionType $type
     *
     * @throws ExceptionInterface
     */
    private function resolveConstructorArgument(ContainerInterface $container, ReflectionParameter $parameter, ReflectionType $type): mixed
    {
        try {
            return $this->resolveParameter($container, $parameter, $type);
        } catch (RuntimeException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new RuntimeException(
                message: 'Failed to resolve constructor argument "' . $parameter->getName() . '" for service "' . $this->id . '"',
                previous: $e
            );
        }
    }

    private function resolveMethodArgument(string $method, ContainerInterface $container, ReflectionParameter $parameter, ReflectionType $type): mixed
    {
        try {
            return $this->resolveParameter($container, $parameter, $type);
        } catch (RuntimeException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new RuntimeException(
                message: 'Failed to resolve method "' . $method . '" argument "' . $parameter->getName() . '" for service "' . $this->id . '"',
                previous: $e
            );
        }
    }

    private function resolveParameter(ContainerInterface $container, ReflectionParameter $parameter, ReflectionType $type): mixed
    {
        if ($type instanceof ReflectionIntersectionType) {
            throw new RuntimeException(
                'Failed to resolve parameter "' . $parameter->getName() . '" because it is an intersection type.'
            );
        }

        if ($type instanceof ReflectionUnionType) {
            $lastException = null;
            foreach ($type->getTypes() as $unionType) {
                try {
                    return $this->resolveParameter($container, $parameter, $unionType);
                } catch (RuntimeException $e) {
                    $lastException = $e;
                }
            }

            throw new RuntimeException(
                message: 'Failed to resolve parameter "' . $parameter->getName() . '" because none of the union types could be resolved.',
                previous: $lastException
            );
        }

        if ($type instanceof ReflectionNamedType) {
            if (!$container->has($type->getName())) {
                throw new RuntimeException(
                    'Failed to resolve parameter "' . $parameter->getName() . '" because it is not a service in the container.'
                );
            }

            return $container->get($type->getName());
        }

        throw new RuntimeException(
            'Failed to resolve parameter "' . $parameter->getName() . '" because it is not a named type.'
        );
    }
}
