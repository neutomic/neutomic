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

namespace Neu\Component\Console\DependencyInjection\Hook;

use Neu\Component\Console\Attribute\Command;
use Neu\Component\Console\Command\CommandInterface;
use Neu\Component\Console\Command\Configuration;
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
    /**
     * The registry service identifier.
     *
     * @var non-empty-string
     */
    private string $registry;

    /**
     * Creates a new {@see RegisterCommandsHook} instance.
     *
     * @param non-empty-string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     */
    public function __construct(null|string $registry = null)
    {
        $this->registry = $registry ?? RegistryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
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
     * @return iterable<Configuration>
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
