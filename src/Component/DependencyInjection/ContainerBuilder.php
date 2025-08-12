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
use Neu\Component\DependencyInjection\Configuration\CombineStrategy;
use Neu\Component\DependencyInjection\Configuration\Document;
use Neu\Component\DependencyInjection\Configuration\DocumentInterface;
use Neu\Component\DependencyInjection\Configuration\Loader\LoaderInterface;
use Neu\Component\DependencyInjection\Configuration\Resolver\Resolver;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
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
use Override;

use function array_walk_recursive;
use function is_string;
use function str_contains;
use function str_replace;

final class ContainerBuilder implements ContainerBuilderInterface
{
    /**
     * The container registry.
     */
    private RegistryInterface $registry;

    /**
     * The configuration resolver.
     */
    private Resolver $resolver;

    /**
     * The combine strategy.
     */
    private CombineStrategy $strategy;

    /**
     * The configuration resources.
     *
     * @var list<mixed>
     */
    private array $resources = [];

    /**
     * Whether auto-discovery is enabled.
     */
    private bool $autoDiscovery = true;

    /**
     * The paths to discover services from.
     *
     * @var list<non-empty-string>
     */
    private array $discoverablePaths = [];

    /**
     * The extensions to apply to the container.
     *
     * @var array<class-string, ExtensionInterface>
     */
    private array $extensions = [];

    /**
     * Create a new {@see ContainerBuilder} instance.
     *
     * @param Project $project The project instance.
     * @param Resolver $resolver The configuration resolver.
     * @param CombineStrategy $strategy The combine strategy to use for combining configuration documents.
     */
    public function __construct(RegistryInterface $registry, Resolver $resolver, CombineStrategy $strategy = CombineStrategy::ReplaceRecursive)
    {
        $this->registry = $registry;
        $this->resolver = $resolver;
        $this->strategy = $strategy;
    }

    /**
     * Create a new container builder.
     *
     * @param Project $project The project instance.
     * @param list<ExtensionInterface> $extensions The extensions to apply to the container.
     *
     * @return static The created container builder.
     */
    public static function create(Project $project, array $extensions = []): static
    {
        $builder = new self(new Registry($project), Resolver::create());
        $builder->addExtensions($extensions);

        return $builder;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addConfigurationResource(mixed $resource): void
    {
        $this->resources[] = $resource;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addConfigurationLoader(LoaderInterface $loader): void
    {
        $this->resolver->addLoader($loader);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addPathForAutoDiscovery(string $path): void
    {
        $this->discoverablePaths[] = $path;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function hasAutoDiscovery(): bool
    {
        return $this->autoDiscovery;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function setAutoDiscovery(bool $autoDiscovery): void
    {
        $this->autoDiscovery = $autoDiscovery;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addExtension(ExtensionInterface $extension): void
    {
        if (isset($this->extensions[$extension::class])) {
            return;
        }

        $this->extensions[$extension::class] = $extension;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function addExtensions(array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function build(): ContainerInterface
    {
        $document = new Document([]);
        /** @var mixed $resource */
        foreach ($this->resources as $resource) {
            $document = $document->combine(
                $this->resolver->resolve($resource)->load($resource),
                $this->strategy,
            );
        }

        $document = $this->processConfiguration($document);

        $this->addCompositeExtensions($document);

        foreach ($this->extensions as $extension) {
            $extension->register($this->registry, $document);
        }

        if ($this->autoDiscovery) {
            $this->doDiscover();
        }

        foreach ($this->registry->getDefinitions() as $definition) {
            foreach ($this->registry->getProcessors() as $processor) {
                $definition->addProcessor($processor);
            }

            foreach ($this->registry->getInstanceOfProcessors() as $interface => $processors) {
                if ($definition->isInstanceOf($interface)) {
                    foreach ($processors as $processor) {
                        $definition->addProcessor($processor);
                    }
                }
            }

            foreach ($this->registry->getAttributeProcessors() as $attribute => $processors) {
                if ($definition->hasAttribute($attribute)) {
                    foreach ($processors as $processor) {
                        $definition->addProcessor($processor);
                    }
                }
            }
        }

        $container = new Container($this->registry->getProject(), $this->registry->getDefinitions());
        foreach ($this->registry->getHooks() as $hook) {
            $hook($container);
        }

        return $container;
    }

    /**
     * Add composite extensions.
     *
     * @param DocumentInterface $document The configuration document used to load the extensions.
     */
    private function addCompositeExtensions(DocumentInterface $document): void
    {
        foreach ($this->extensions as $extension) {
            if ($extension instanceof CompositeExtensionInterface) {
                $this->addCompositeExtension($extension, $document);
            }
        }
    }

    /**
     * Add composite extensions.
     *
     * @param CompositeExtensionInterface $extension The extension to add.
     * @param DocumentInterface $document The configuration document used to load the sub-extensions.
     */
    private function addCompositeExtension(CompositeExtensionInterface $extension, DocumentInterface $document): void
    {
        $extensions = $extension->getExtensions($document);

        foreach ($extensions as $extension) {
            $this->addExtension($extension);

            if ($extension instanceof CompositeExtensionInterface) {
                $this->addCompositeExtension($extension, $document);
            }
        }
    }

    /**
     * Discover services.
     *
     * @throws RuntimeException If an error occurs while discovering services.
     */
    private function doDiscover(): void
    {
        if ([] === $this->discoverablePaths) {
            return;
        }

        $astLocator = (new BetterReflection())->astLocator();
        $locators = [
            new AutoloadSourceLocator($astLocator),
        ];
        foreach ($this->discoverablePaths as $path) {
            try {
                if (File\isFile($path)) {
                    $locators[] = new SingleFileSourceLocator($path, $astLocator);
                } elseif (File\isDirectory($path)) {
                    $locators[] = new DirectoriesSourceLocator([$path], $astLocator);
                }
            } catch (InvalidDirectory | InvalidFileInfo | InvalidFileLocation $exception) {
                throw new RuntimeException('An error occurred while discovering services from "' . $path . '"', previous: $exception);
            }
        }

        $sourceLocator = new AggregateSourceLocator($locators);
        $reflector = new DefaultReflector($sourceLocator);
        foreach ($reflector->reflectAllClasses() as $class) {
            if (!$class->isInstantiable() || $class->isAnonymous()) {
                continue;
            }

            $name = $class->getName();
            foreach ($this->registry->getDefinitions() as $definition) {
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

            $this->registry->addDefinition(Definition\Definition::create($name, $name));
        }
    }

    /**
     * Process the configuration.
     *
     * @param DocumentInterface $document The configuration document to process.
     *
     * @return DocumentInterface The processed configuration document.
     */
    private function processConfiguration(DocumentInterface $document): DocumentInterface
    {
        $project = $this->registry->getProject();
        $configurations = $document->getAll();

        $placeholders = $project->getPlaceholders();
        array_walk_recursive($configurations, static function (mixed &$value) use ($placeholders): void {
            if (!is_string($value) || '' === $value) {
                return;
            }

            foreach ($placeholders as $placeholder => $replacement) {
                if (str_contains($value, $placeholder)) {
                    $value = str_replace($placeholder, $replacement, $value);
                }
            }
        });

        return new Document($configurations);
    }
}
