<?php

declare(strict_types=1);

namespace Neu\Component\Console\DependencyInjection\Factory;

use Neu\Component\Console\Application;
use Neu\Component\Console\Command\Registry\RegistryInterface;
use Neu\Component\Console\Configuration;
use Neu\Component\Console\Recovery\RecoveryInterface;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;

/**
 * A factory for creating a new instance of the {@see Application}.
 *
 * @implements FactoryInterface<Application>
 */
final readonly class ApplicationFactory implements FactoryInterface
{
    private string $configuration;
    private string $registry;
    private string $recovery;

    /**
     * Creates a new {@see ApplicationFactory} instance.
     *
     * @param string|null $configuration The configuration service identifier, defaults to {@see Configuration::class}.
     * @param string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     * @param string|null $recovery The recovery service identifier, defaults to {@see RecoveryInterface::class}.
     */
    public function __construct(?string $configuration = null, ?string $registry = null, ?string $recovery = null)
    {
        $this->configuration = $configuration ?? Configuration::class;
        $this->registry = $registry ?? RegistryInterface::class;
        $this->recovery = $recovery ?? RecoveryInterface::class;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): object
    {
        return new Application(
            $container->getTyped($this->configuration, Configuration::class),
            $container->getTyped($this->registry, RegistryInterface::class),
            $container->getTyped($this->recovery, RecoveryInterface::class),
        );
    }
}
