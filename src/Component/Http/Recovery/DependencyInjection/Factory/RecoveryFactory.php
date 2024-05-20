<?php

declare(strict_types=1);

namespace Neu\Component\Http\Recovery\DependencyInjection\Factory;

use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Neu\Component\Http\Recovery\Recovery;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Recovery} instance.
 *
 * @psalm-import-type ThrowablesConfigurationType from Recovery
 *
 * @implements FactoryInterface<Recovery>
 */
final readonly class RecoveryFactory implements FactoryInterface
{
    /**
     * The logger service identifier.
     */
    private string $logger;

    /**
     * @param ThrowablesConfigurationType $throwables
     */
    private array $throwables;

    /**
     * @param non-empty-string|null $logger The logger service identifier.
     * @param ThrowablesConfigurationType $throwables The throwables configuration.
     */
    public function __construct(?string $logger = null, array $throwables = [])
    {
        $this->logger = $logger ?? LoggerInterface::class;
        $this->throwables = $throwables;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container): Recovery
    {
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Recovery($container->getProject()->debug, $logger, $this->throwables);
    }
}
