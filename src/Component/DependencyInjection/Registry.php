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
use Psl\Iter;

final class Registry implements RegistryInterface
{
    /**
     * The project instance.
     */
    private Project $project;

    /**
     * The service definitions.
     *
     * @var array<non-empty-string, DefinitionInterface>
     */
    private array $definitions = [];

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
     * Create a new {@see Registry} instance.
     *
     * @param Project $project The project instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Create a new {@see Registry} instance.
     *
     * @param Project $project The project instance.
     */
    public static function create(Project $project): self
    {
        return new self($project);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addDefinition(DefinitionInterface $definition): void
    {
        $this->definitions[$definition->getId()] = $definition;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $this->addDefinition($definition);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function hasDefinition(string $id): bool
    {
        foreach ($this->definitions as $definition) {
            if ($definition->getId() === $id) {
                return true;
            }

            if (Iter\contains($definition->getAliases(), $id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDefinition(string $id): DefinitionInterface
    {
        return $this->definitions[$id] ?? throw new Exception\ServiceNotFoundException($id);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addProcessor(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addProcessorForInstanceOf(string $type, ProcessorInterface $processor): void
    {
        $processorsForInstanceOf = $this->processorsForInstanceOf[$type] ?? [];
        $processorsForInstanceOf[] = $processor;

        $this->processorsForInstanceOf[$type] = $processorsForInstanceOf;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addProcessorForAttribute(string $attribute, ProcessorInterface $processor): void
    {
        $processorsForAttributes = $this->processorsForAttributes[$attribute] ?? [];
        $processorsForAttributes[] = $processor;

        $this->processorsForAttributes[$attribute] = $processorsForAttributes;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getProcessors(): array
    {
        return $this->processors;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getInstanceOfProcessors(): array
    {
        return $this->processorsForInstanceOf;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAttributeProcessors(): array
    {
        return $this->processorsForAttributes;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addHook(HookInterface $hook): void
    {
        $this->hooks[] = $hook;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function addHooks(array $hooks): void
    {
        foreach ($hooks as $hook) {
            $this->hooks[] = $hook;
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getHooks(): array
    {
        return $this->hooks;
    }
}
