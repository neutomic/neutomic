<?php

declare(strict_types=1);

namespace Neu\Component\Console\DependencyInjection\Hook;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\Registry\RegistryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Exception\RuntimeException;
use Neu\Component\DependencyInjection\HookInterface;
use ReflectionAttribute;
use ReflectionObject;

/**
 * A hook for registering commands in the {@see RegistryInterface}.
 */
final readonly class RegisterCommandsHook implements HookInterface
{
    private string $registry;

    /**
     * Creates a new {@see RegisterCommandsHook} instance.
     *
     * @param string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     */
    public function __construct(?string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): void
    {
        $registry = $container->getTyped($this->registry, RegistryInterface::class);

        foreach ($container->getAttributed(Command::class) as $command) {
            if (!$command instanceof CommandInterface) {
                throw new RuntimeException('The command "' . $command::class . '" must implement "' . CommandInterface::class . '".');
            }

            foreach ($this->readConfiguration($command) as $configuration) {
                $registry->register($configuration, $command);
            }
        }
    }

    /**
     * @return iterable<Command>
     */
    private function readConfiguration(CommandInterface $command): iterable
    {
        $reflection = new ReflectionObject($command);
        $attributes = $reflection->getAttributes(Command::class, ReflectionAttribute::IS_INSTANCEOF);
        $configurations = [];
        foreach ($attributes as $attribute) {
            $configurations[] = $attribute->newInstance()->getConfiguration();
        }

        return $configurations;
    }
}
