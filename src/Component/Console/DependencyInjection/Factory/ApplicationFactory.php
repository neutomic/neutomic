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
    /**
     * The configuration service identifier.
     *
     * @var non-empty-string
     */
    private string $configuration;

    /**
     * The registry service identifier.
     *
     * @var non-empty-string
     */
    private string $registry;

    /**
     * The recovery service identifier.
     *
     * @var non-empty-string
     */
    private string $recovery;

    /**
     * Creates a new {@see ApplicationFactory} instance.
     *
     * @param non-empty-string|null $configuration The configuration service identifier, defaults to {@see Configuration::class}.
     * @param non-empty-string|null $registry The registry service identifier, defaults to {@see RegistryInterface::class}.
     * @param non-empty-string|null $recovery The recovery service identifier, defaults to {@see RecoveryInterface::class}.
     */
    public function __construct(null|string $configuration = null, null|string $registry = null, null|string $recovery = null)
    {
        $this->configuration = $configuration ?? Configuration::class;
        $this->registry = $registry ?? RegistryInterface::class;
        $this->recovery = $recovery ?? RecoveryInterface::class;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function __invoke(ContainerInterface $container): object
    {
        return new Application(
            $container->getTyped($this->configuration, Configuration::class),
            $container->getTyped($this->registry, RegistryInterface::class),
            $container->getTyped($this->recovery, RecoveryInterface::class),
        );
    }
}
