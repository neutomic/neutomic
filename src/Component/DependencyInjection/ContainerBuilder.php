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

use Amp\File;
use Neu\Component\Configuration\ConfigurationContainer;
use Neu\Component\Configuration\ConfigurationContainerInterface;
use Neu\Component\DependencyInjection\Definition\DefinitionInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Psl\Iter;
use ReflectionClass;
use ReflectionException;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Exception\InvalidDirectory;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileInfo;
use Roave\BetterReflection\SourceLocator\Exception\InvalidFileLocation;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

final class ContainerBuilder implements ContainerBuilderInterface
{
    /**
     * The project instance.
     *
     * @var Project
     */
    private Project $project;

    /**
     * The configuration container.
     *
     * @var ConfigurationContainerInterface
     */
    private ConfigurationContainerInterface $configuration;

    /**
     * Whether auto-discovery is enabled.
     */
    private bool $autoDiscovery = true;

    /**
     * The service definitions.
     *
     * @var array<non-empty-string, DefinitionInterface>
     */
    private array $definitions = [];

    /**
     * The extensions to apply to the container.
     *
     * @var array<class-string, ExtensionInterface>
     */
    private array $extensions = [];

    /**
     * The hooks to apply to the container.
     *
     * @var list<HookInterface>
     */
    private array $hooks = [];

    /**
     * The processors to apply to the services.
     *
     * @var list<ProcessorInterface>
     */
    private array $processors = [];


    /**
     * The processors to apply to the services by interface.
     *
     * @var array<class-string, list<ProcessorInterface>>
     */
    private array $processorsForInstanceOf = [];

    /**
     * The processors to apply to the services by attribute.
     *
     * @var array<class-string, list<ProcessorInterface>>
     */
    private array $processorsForAttributes = [];

    /**
     * Create a new container builder.
     *
     * @param Project $project The project instance.
     * @param ConfigurationContainerInterface|null $configuration The configuration container.
     */
    public function __construct(Project $project, null|ConfigurationContainerInterface $configuration = null)
    {
        $this->project = $project;
        $this->configuration = $configuration ?? new ConfigurationContainer([]);
    }

    /**
     * Create a new container builder.
     *
     * @param Project $project The project instance.
     * @param ConfigurationContainerInterface|null $configuration The configuration container.
     *
     * @return static The created container builder.
     */
    public static function create(Project $project, null|ConfigurationContainerInterface $configuration = null): static
    {
        return new self($project, $configuration);
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
    public function getConfiguration(): ConfigurationContainerInterface
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public function hasAutoDiscovery(): bool
    {
        return $this->autoDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function setAutoDiscovery(bool $autoDiscovery): void
    {
        $this->autoDiscovery = $autoDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function hasExtension(string $extension): bool
    {
        return Iter\contains_key($this->extensions, $extension);
    }

    /**
     * @inheritDoc
     */
    public function addExtension(ExtensionInterface $extension): void
    {
        $this->extensions[$extension::class] = $extension;
    }

    /**
     * @inheritDoc
     */
    public function addExtensions(array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * @inheritDoc
     */
    public function addHook(HookInterface $hook): void
    {
        $this->hooks[] = $hook;
    }

    /**
     * @inheritDoc
     */
    public function addHooks(array $hooks): void
    {
        foreach ($hooks as $hook) {
            $this->hooks[] = $hook;
        }
    }

    /**
     * @inheritDoc
     */
    public function addConfiguration(ConfigurationContainerInterface|array $configuration): void
    {
        if (!$configuration instanceof ConfigurationContainerInterface) {
            $configuration = new ConfigurationContainer($configuration);
        }

        $this->configuration = $this->configuration->merge($configuration);
    }

    /**
     * @inheritDoc
     */
    public function hasDefinition(string $id): bool
    {
        return Iter\contains_key($this->definitions, $id);
    }

    /**
     * @inheritDoc
     */
    public function getDefinition(string $id): DefinitionInterface
    {
        return $this->definitions[$id] ?? throw new Exception\ServiceNotFoundException($id);
    }

    /**
     * @inheritDoc
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @inheritDoc
     */
    public function addDefinition(DefinitionInterface $definition): void
    {
        $this->definitions[$definition->getId()] = $definition;
    }

    /**
     * @inheritDoc
     */
    public function addDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $this->addDefinition($definition);
        }
    }

    /**
     * @inheritDoc
     */
    public function addProcessor(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /**
     * @inheritDoc
     */
    public function addProcessorForInstanceOf(string $type, ProcessorInterface $processor): void
    {
        $processorsForInstanceOf = $this->processorsForInstanceOf[$type] ?? [];
        $processorsForInstanceOf[] = $processor;

        $this->processorsForInstanceOf[$type] = $processorsForInstanceOf;
    }

    /**
     * @inheritDoc
     */
    public function addProcessorForAttribute(string $attribute, ProcessorInterface $processor): void
    {
        $processorsForAttributes = $this->processorsForAttributes[$attribute] ?? [];
        $processorsForAttributes[] = $processor;

        $this->processorsForAttributes[$attribute] = $processorsForAttributes;
    }

    /**
     * @inheritDoc
     */
    public function build(): ContainerInterface
    {
        $clone = clone $this;

        foreach ($clone->extensions as $extension) {
            $extension->register($clone);
        }

        if ($clone->autoDiscovery) {
            $clone->discover();
        }

        foreach ($clone->definitions as $definition) {
            foreach ($clone->processors as $processor) {
                $definition->addProcessor($processor);
            }

            foreach ($clone->processorsForInstanceOf as $interface => $processors) {
                if ($definition->isInstanceOf($interface)) {
                    foreach ($processors as $processor) {
                        $definition->addProcessor($processor);
                    }
                }
            }

            foreach ($clone->processorsForAttributes as $attribute => $processors) {
                if ($definition->hasAttribute($attribute)) {
                    foreach ($processors as $processor) {
                        $definition->addProcessor($processor);
                    }
                }
            }
        }

        $container = new Container($clone->project, $clone->definitions);
        foreach ($clone->hooks as $hook) {
            $hook($container);
        }

        return $container;
    }

    /**
     * Discover services.
     *
     * @throws RuntimeException If an error occurs while discovering services.
     */
    private function discover(): void
    {
        $entrypoint = $this->project->entrypoint;
        $source = $this->project->source;

        $astLocator = (new BetterReflection())->astLocator();

        try {
            $locators = [
                new SingleFileSourceLocator($entrypoint, $astLocator),
                new AutoloadSourceLocator($astLocator),
            ];

            if (null !== $source) {
                if (!File\exists($source)) {
                    throw new RuntimeException('The source "' . $source . '" does not exist.');
                } elseif (File\isFile($source)) {
                    $locators[] = new SingleFileSourceLocator($source, $astLocator);
                } elseif (File\isDirectory($source)) {
                    $locators[] = new DirectoriesSourceLocator([$source], $astLocator);
                }
            }
        } catch (InvalidDirectory | InvalidFileInfo | InvalidFileLocation $exception) {
            throw new RuntimeException('An error occurred while discovering services.', previous: $exception);
        }

        $sourceLocator = new AggregateSourceLocator($locators);
        $reflector = new DefaultReflector($sourceLocator);
        foreach ($reflector->reflectAllClasses() as $class) {
            if (!$class->isInstantiable() || $class->isAnonymous()) {
                continue;
            }

            $name = $class->getName();
            foreach ($this->definitions as $definition) {
                if ($definition->getId() === $name) {
                    continue 2;
                }

                if ($definition->getType() === $name) {
                    $definition->addAlias($name);

                    continue 2;
                }
            }

            try {
                $reflection = new ReflectionClass($class->getName());
            } catch (ReflectionException $e) {
                throw new RuntimeException('Failed to reflect class: ' . $name, previous: $e);
            }

            if (!$reflection->isInstantiable()) {
                continue;
            }

            $this->addDefinition(Definition\Definition::create($name, $name));
        }
    }
}
