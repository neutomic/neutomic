<?php

declare(strict_types=1);

namespace Neu\Component\Advisory\DependencyInjection\Factory;

use Neu\Component\Advisory\Advisory;
use Neu\Component\DependencyInjection\ContainerInterface;
use Neu\Component\DependencyInjection\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Factory for creating a {@see Advisory} instance.
 *
 * @implements FactoryInterface<Advisory>
 */
final readonly class AdvisoryFactory implements FactoryInterface
{
    private string $logger;

    public function __construct(?string $logger = null)
    {
        $this->logger = $logger ?? LoggerInterface::class;
    }

    public function __invoke(ContainerInterface $container): object
    {
        $logger = $container->getTyped($this->logger, LoggerInterface::class);

        return new Advisory($logger);
    }
}
